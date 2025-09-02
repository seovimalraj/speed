<?php
/**
 * License management system
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_License {
    
    private $license_key;
    private $license_status;
    private $license_expires;
    private $api_url = 'https://secure.fastspring.com/';
    
    // License tiers
    const TIER_FREE = 'free';
    const TIER_PREMIUM = 'premium';
    const TIER_BUSINESS = 'business';
    const TIER_AGENCY = 'agency';
    
    public function __construct() {
        $this->license_key = get_option('speed_optimizer_license_key', '');
        $this->license_status = get_option('speed_optimizer_license_status', self::TIER_FREE);
        $this->license_expires = get_option('speed_optimizer_license_expires', '');
        
        add_action('init', array($this, 'init_hooks'));
    }
    
    /**
     * Initialize WordPress hooks
     */
    public function init_hooks() {
        add_action('wp_ajax_speed_optimizer_activate_license', array($this, 'ajax_activate_license'));
        add_action('wp_ajax_speed_optimizer_deactivate_license', array($this, 'ajax_deactivate_license'));
        add_action('wp_ajax_speed_optimizer_check_license', array($this, 'ajax_check_license'));
        
        // Schedule daily license check
        if (!wp_next_scheduled('speed_optimizer_daily_license_check')) {
            wp_schedule_event(time(), 'daily', 'speed_optimizer_daily_license_check');
        }
        add_action('speed_optimizer_daily_license_check', array($this, 'check_license_status'));
    }
    
    /**
     * Get current license tier
     */
    public function get_license_tier() {
        if (empty($this->license_key)) {
            return self::TIER_FREE;
        }
        
        // Check if license is expired
        if (!empty($this->license_expires) && strtotime($this->license_expires) < time()) {
            return self::TIER_FREE;
        }
        
        return $this->license_status;
    }
    
    /**
     * Check if feature is available for current license
     */
    public function is_feature_available($feature) {
        $tier = $this->get_license_tier();
        $feature_map = $this->get_feature_map();
        
        if (!isset($feature_map[$feature])) {
            return false;
        }
        
        $required_tiers = $feature_map[$feature];
        return in_array($tier, $required_tiers);
    }
    
    /**
     * Get feature availability mapping
     */
    private function get_feature_map() {
        return array(
            // Free features
            'basic_caching' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'basic_minification' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'gzip_compression' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'basic_lazy_loading' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'simple_database_cleanup' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'basic_dashboard' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'page_caching' => array(self::TIER_FREE, self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            
            // Premium features
            'advanced_caching' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'object_caching' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'fragment_caching' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'critical_css' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'advanced_minification' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'webp_conversion' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'video_optimization' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'cdn_integration' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'prefetching' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'advanced_analytics' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'priority_support' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            
            // New Premium features
            'cache_preloading' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'file_concatenation' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'remove_unused_css' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'delay_js_execution' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'defer_js_loading' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'defer_css_loading' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'inline_critical_css' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'heartbeat_control' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'preload_fonts' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'cloudflare_integration' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            'varnish_cache' => array(self::TIER_PREMIUM, self::TIER_BUSINESS, self::TIER_AGENCY),
            
            // Business features
            'multisite_support' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            'scheduled_optimization' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            'advanced_reporting' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            'advanced_cache_rules' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            'user_agent_cache' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            'cache_logged_users' => array(self::TIER_BUSINESS, self::TIER_AGENCY),
            
            // Agency features
            'white_labeling' => array(self::TIER_AGENCY),
            'client_management' => array(self::TIER_AGENCY),
            'custom_branding' => array(self::TIER_AGENCY),
        );
    }
    
    /**
     * Activate license
     */
    public function activate_license($license_key) {
        $response = $this->make_api_request('activate', array(
            'license_key' => $license_key,
            'site_url' => get_site_url()
        ));
        
        if ($response && isset($response['status']) && $response['status'] === 'active') {
            update_option('speed_optimizer_license_key', $license_key);
            update_option('speed_optimizer_license_status', $response['tier']);
            update_option('speed_optimizer_license_expires', $response['expires']);
            
            $this->license_key = $license_key;
            $this->license_status = $response['tier'];
            $this->license_expires = $response['expires'];
            
            return array('success' => true, 'message' => 'License activated successfully');
        }
        
        return array('success' => false, 'message' => 'Failed to activate license');
    }
    
    /**
     * Deactivate license
     */
    public function deactivate_license() {
        $response = $this->make_api_request('deactivate', array(
            'license_key' => $this->license_key,
            'site_url' => get_site_url()
        ));
        
        delete_option('speed_optimizer_license_key');
        delete_option('speed_optimizer_license_status');
        delete_option('speed_optimizer_license_expires');
        
        $this->license_key = '';
        $this->license_status = self::TIER_FREE;
        $this->license_expires = '';
        
        return array('success' => true, 'message' => 'License deactivated successfully');
    }
    
    /**
     * Check license status
     */
    public function check_license_status() {
        if (empty($this->license_key)) {
            return;
        }
        
        $response = $this->make_api_request('check', array(
            'license_key' => $this->license_key,
            'site_url' => get_site_url()
        ));
        
        if ($response && isset($response['status'])) {
            if ($response['status'] === 'active') {
                update_option('speed_optimizer_license_status', $response['tier']);
                update_option('speed_optimizer_license_expires', $response['expires']);
                $this->license_status = $response['tier'];
                $this->license_expires = $response['expires'];
            } else {
                // License is invalid or expired
                update_option('speed_optimizer_license_status', self::TIER_FREE);
                $this->license_status = self::TIER_FREE;
            }
        }
    }
    
    /**
     * Make API request to license server
     */
    private function make_api_request($action, $data) {
        // For demo purposes, we'll simulate API responses
        // In production, this would make actual API calls to FastSpring
        
        switch ($action) {
            case 'activate':
                // Simulate license validation
                if (strlen($data['license_key']) >= 16) {
                    $tier = $this->determine_tier_from_key($data['license_key']);
                    return array(
                        'status' => 'active',
                        'tier' => $tier,
                        'expires' => date('Y-m-d H:i:s', strtotime('+1 year'))
                    );
                }
                return array('status' => 'invalid');
                
            case 'check':
                // Simulate license check
                return array(
                    'status' => 'active',
                    'tier' => $this->license_status,
                    'expires' => $this->license_expires
                );
                
            case 'deactivate':
                return array('status' => 'deactivated');
        }
        
        return false;
    }
    
    /**
     * Determine tier from license key (demo implementation)
     */
    private function determine_tier_from_key($key) {
        if (strpos($key, 'AGENCY') !== false) {
            return self::TIER_AGENCY;
        } elseif (strpos($key, 'BUSINESS') !== false) {
            return self::TIER_BUSINESS;
        } elseif (strpos($key, 'PREMIUM') !== false) {
            return self::TIER_PREMIUM;
        }
        return self::TIER_PREMIUM; // Default to premium for valid keys
    }
    
    /**
     * AJAX handler for license activation
     */
    public function ajax_activate_license() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $license_key = sanitize_text_field($_POST['license_key']);
        $result = $this->activate_license($license_key);
        
        wp_send_json($result);
    }
    
    /**
     * AJAX handler for license deactivation
     */
    public function ajax_deactivate_license() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $result = $this->deactivate_license();
        wp_send_json($result);
    }
    
    /**
     * AJAX handler for license check
     */
    public function ajax_check_license() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $this->check_license_status();
        
        wp_send_json(array(
            'success' => true,
            'tier' => $this->get_license_tier(),
            'expires' => $this->license_expires
        ));
    }
    
    /**
     * Get license information
     */
    public function get_license_info() {
        return array(
            'key' => $this->license_key,
            'tier' => $this->get_license_tier(),
            'expires' => $this->license_expires,
            'is_valid' => !empty($this->license_key) && (empty($this->license_expires) || strtotime($this->license_expires) > time())
        );
    }
    
    /**
     * Get tier display name
     */
    public function get_tier_display_name($tier = null) {
        if ($tier === null) {
            $tier = $this->get_license_tier();
        }
        
        $names = array(
            self::TIER_FREE => __('Free', 'speed-optimizer'),
            self::TIER_PREMIUM => __('Premium', 'speed-optimizer'),
            self::TIER_BUSINESS => __('Business', 'speed-optimizer'),
            self::TIER_AGENCY => __('Agency', 'speed-optimizer')
        );
        
        return isset($names[$tier]) ? $names[$tier] : $names[self::TIER_FREE];
    }
}