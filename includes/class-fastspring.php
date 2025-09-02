<?php
/**
 * FastSpring payment integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_FastSpring {
    
    private $store_id;
    private $api_username;
    private $api_password;
    private $api_url;
    
    public function __construct() {
        $this->store_id = get_option('speed_optimizer_fastspring_store_id', '');
        $this->api_username = get_option('speed_optimizer_fastspring_api_username', '');
        $this->api_password = get_option('speed_optimizer_fastspring_api_password', '');
        $this->api_url = 'https://api.fastspring.com/';
        
        add_action('init', array($this, 'init_hooks'));
    }
    
    /**
     * Initialize WordPress hooks
     */
    public function init_hooks() {
        add_action('wp_ajax_speed_optimizer_create_checkout', array($this, 'ajax_create_checkout'));
        add_action('wp_ajax_nopriv_speed_optimizer_fastspring_webhook', array($this, 'handle_webhook'));
        add_action('wp_ajax_speed_optimizer_fastspring_webhook', array($this, 'handle_webhook'));
    }
    
    /**
     * Get available plans
     */
    public function get_plans() {
        return array(
            'premium_monthly' => array(
                'id' => 'speed-optimizer-premium-monthly',
                'name' => __('Premium Monthly', 'speed-optimizer'),
                'price' => '$9.99',
                'period' => __('per month', 'speed-optimizer'),
                'features' => array(
                    __('Advanced caching mechanisms', 'speed-optimizer'),
                    __('Critical CSS generation', 'speed-optimizer'),
                    __('WebP image conversion', 'speed-optimizer'),
                    __('Video optimization', 'speed-optimizer'),
                    __('CDN integration', 'speed-optimizer'),
                    __('Prefetching & preloading', 'speed-optimizer'),
                    __('Priority support', 'speed-optimizer'),
                    __('Advanced analytics', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_PREMIUM
            ),
            'premium_yearly' => array(
                'id' => 'speed-optimizer-premium-yearly',
                'name' => __('Premium Yearly', 'speed-optimizer'),
                'price' => '$99.99',
                'period' => __('per year', 'speed-optimizer'),
                'discount' => __('Save 17%', 'speed-optimizer'),
                'features' => array(
                    __('Advanced caching mechanisms', 'speed-optimizer'),
                    __('Critical CSS generation', 'speed-optimizer'),
                    __('WebP image conversion', 'speed-optimizer'),
                    __('Video optimization', 'speed-optimizer'),
                    __('CDN integration', 'speed-optimizer'),
                    __('Prefetching & preloading', 'speed-optimizer'),
                    __('Priority support', 'speed-optimizer'),
                    __('Advanced analytics', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_PREMIUM
            ),
            'business_monthly' => array(
                'id' => 'speed-optimizer-business-monthly',
                'name' => __('Business Monthly', 'speed-optimizer'),
                'price' => '$29.99',
                'period' => __('per month', 'speed-optimizer'),
                'features' => array(
                    __('All Premium features', 'speed-optimizer'),
                    __('Multisite support', 'speed-optimizer'),
                    __('Scheduled optimization', 'speed-optimizer'),
                    __('Advanced reporting', 'speed-optimizer'),
                    __('Up to 10 sites', 'speed-optimizer'),
                    __('Phone support', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_BUSINESS
            ),
            'business_yearly' => array(
                'id' => 'speed-optimizer-business-yearly',
                'name' => __('Business Yearly', 'speed-optimizer'),
                'price' => '$299.99',
                'period' => __('per year', 'speed-optimizer'),
                'discount' => __('Save 17%', 'speed-optimizer'),
                'features' => array(
                    __('All Premium features', 'speed-optimizer'),
                    __('Multisite support', 'speed-optimizer'),
                    __('Scheduled optimization', 'speed-optimizer'),
                    __('Advanced reporting', 'speed-optimizer'),
                    __('Up to 10 sites', 'speed-optimizer'),
                    __('Phone support', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_BUSINESS
            ),
            'agency_monthly' => array(
                'id' => 'speed-optimizer-agency-monthly',
                'name' => __('Agency Monthly', 'speed-optimizer'),
                'price' => '$99.99',
                'period' => __('per month', 'speed-optimizer'),
                'features' => array(
                    __('All Business features', 'speed-optimizer'),
                    __('White-labeling', 'speed-optimizer'),
                    __('Custom branding', 'speed-optimizer'),
                    __('Client management', 'speed-optimizer'),
                    __('Unlimited sites', 'speed-optimizer'),
                    __('Dedicated support', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_AGENCY
            ),
            'agency_yearly' => array(
                'id' => 'speed-optimizer-agency-yearly',
                'name' => __('Agency Yearly', 'speed-optimizer'),
                'price' => '$999.99',
                'period' => __('per year', 'speed-optimizer'),
                'discount' => __('Save 17%', 'speed-optimizer'),
                'features' => array(
                    __('All Business features', 'speed-optimizer'),
                    __('White-labeling', 'speed-optimizer'),
                    __('Custom branding', 'speed-optimizer'),
                    __('Client management', 'speed-optimizer'),
                    __('Unlimited sites', 'speed-optimizer'),
                    __('Dedicated support', 'speed-optimizer')
                ),
                'tier' => Speed_Optimizer_License::TIER_AGENCY
            )
        );
    }
    
    /**
     * Create checkout session
     */
    public function create_checkout($plan_id, $customer_data = array()) {
        if (empty($this->store_id)) {
            return array('success' => false, 'message' => 'FastSpring not configured');
        }
        
        $plans = $this->get_plans();
        if (!isset($plans[$plan_id])) {
            return array('success' => false, 'message' => 'Invalid plan');
        }
        
        $plan = $plans[$plan_id];
        
        // Create FastSpring checkout URL
        $checkout_url = sprintf(
            'https://%s.onfastspring.com/popup-%s',
            $this->store_id,
            $plan['id']
        );
        
        // Add customer data and return URL
        $params = array(
            'contact_email' => isset($customer_data['email']) ? $customer_data['email'] : '',
            'contact_fname' => isset($customer_data['first_name']) ? $customer_data['first_name'] : '',
            'contact_lname' => isset($customer_data['last_name']) ? $customer_data['last_name'] : '',
            'referrer' => get_site_url(),
            'webhook_url' => admin_url('admin-ajax.php?action=speed_optimizer_fastspring_webhook')
        );
        
        $checkout_url = add_query_arg($params, $checkout_url);
        
        return array(
            'success' => true,
            'checkout_url' => $checkout_url,
            'plan' => $plan
        );
    }
    
    /**
     * Handle FastSpring webhook
     */
    public function handle_webhook() {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['type'])) {
            wp_die('Invalid webhook data', '', 400);
        }
        
        switch ($data['type']) {
            case 'order.completed':
                $this->handle_order_completed($data);
                break;
                
            case 'subscription.activated':
                $this->handle_subscription_activated($data);
                break;
                
            case 'subscription.canceled':
                $this->handle_subscription_canceled($data);
                break;
                
            case 'subscription.charge.completed':
                $this->handle_subscription_renewed($data);
                break;
        }
        
        wp_die('OK', '', 200);
    }
    
    /**
     * Handle order completed
     */
    private function handle_order_completed($data) {
        if (!isset($data['data']['order']['items'])) {
            return;
        }
        
        foreach ($data['data']['order']['items'] as $item) {
            $license_key = $this->generate_license_key();
            $plan_id = $item['product'];
            $tier = $this->get_tier_from_plan_id($plan_id);
            
            // Store license information
            $license_data = array(
                'license_key' => $license_key,
                'tier' => $tier,
                'customer_email' => $data['data']['order']['customer']['email'],
                'fastspring_order_id' => $data['data']['order']['id'],
                'fastspring_subscription_id' => isset($item['subscription']) ? $item['subscription'] : '',
                'created_at' => current_time('mysql'),
                'expires_at' => $this->calculate_expiration($plan_id)
            );
            
            $this->store_license_data($license_data);
            
            // Send license email
            $this->send_license_email($license_data);
        }
    }
    
    /**
     * Handle subscription activated
     */
    private function handle_subscription_activated($data) {
        // Update license status to active
        $subscription_id = $data['data']['subscription']['id'];
        $this->update_license_by_subscription($subscription_id, 'active');
    }
    
    /**
     * Handle subscription canceled
     */
    private function handle_subscription_canceled($data) {
        // Update license status to canceled
        $subscription_id = $data['data']['subscription']['id'];
        $this->update_license_by_subscription($subscription_id, 'canceled');
    }
    
    /**
     * Handle subscription renewed
     */
    private function handle_subscription_renewed($data) {
        // Extend license expiration
        $subscription_id = $data['data']['subscription']['id'];
        $this->extend_license_by_subscription($subscription_id);
    }
    
    /**
     * Generate license key
     */
    private function generate_license_key() {
        return strtoupper(wp_generate_password(24, false));
    }
    
    /**
     * Get tier from plan ID
     */
    private function get_tier_from_plan_id($plan_id) {
        $plans = $this->get_plans();
        
        foreach ($plans as $plan) {
            if ($plan['id'] === $plan_id) {
                return $plan['tier'];
            }
        }
        
        return Speed_Optimizer_License::TIER_PREMIUM;
    }
    
    /**
     * Calculate expiration date
     */
    private function calculate_expiration($plan_id) {
        $period = strpos($plan_id, 'yearly') !== false ? '+1 year' : '+1 month';
        return date('Y-m-d H:i:s', strtotime($period));
    }
    
    /**
     * Store license data
     */
    private function store_license_data($license_data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_licenses';
        
        $wpdb->insert(
            $table_name,
            $license_data,
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }
    
    /**
     * Update license by subscription
     */
    private function update_license_by_subscription($subscription_id, $status) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_licenses';
        
        $wpdb->update(
            $table_name,
            array('status' => $status),
            array('fastspring_subscription_id' => $subscription_id),
            array('%s'),
            array('%s')
        );
    }
    
    /**
     * Extend license by subscription
     */
    private function extend_license_by_subscription($subscription_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_licenses';
        
        // Get current license
        $license = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE fastspring_subscription_id = %s",
            $subscription_id
        ));
        
        if ($license) {
            $new_expiration = date('Y-m-d H:i:s', strtotime($license->expires_at . ' +1 month'));
            
            $wpdb->update(
                $table_name,
                array('expires_at' => $new_expiration),
                array('id' => $license->id),
                array('%s'),
                array('%d')
            );
        }
    }
    
    /**
     * Send license email
     */
    private function send_license_email($license_data) {
        $subject = __('Your Speed Optimizer License Key', 'speed-optimizer');
        $message = sprintf(
            __('Thank you for purchasing Speed Optimizer! Here is your license key: %s', 'speed-optimizer'),
            $license_data['license_key']
        );
        
        wp_mail($license_data['customer_email'], $subject, $message);
    }
    
    /**
     * AJAX handler for creating checkout
     */
    public function ajax_create_checkout() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        $plan_id = sanitize_text_field($_POST['plan_id']);
        $customer_data = array(
            'email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name'])
        );
        
        $result = $this->create_checkout($plan_id, $customer_data);
        wp_send_json($result);
    }
    
    /**
     * Create license table
     */
    public static function create_license_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_licenses';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            license_key varchar(255) NOT NULL,
            tier varchar(50) NOT NULL,
            status varchar(50) DEFAULT 'active',
            customer_email varchar(255) NOT NULL,
            fastspring_order_id varchar(255),
            fastspring_subscription_id varchar(255),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            expires_at datetime,
            PRIMARY KEY (id),
            UNIQUE KEY license_key (license_key),
            KEY customer_email (customer_email),
            KEY fastspring_subscription_id (fastspring_subscription_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}