<?php
/**
 * Multi-site support functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_Multisite {
    
    private $license;
    
    public function __construct() {
        $this->license = new Speed_Optimizer_License();
        
        if (is_multisite() && $this->license->is_feature_available('multisite_support')) {
            $this->init_hooks();
        }
    }
    
    /**
     * Initialize WordPress hooks for multisite
     */
    private function init_hooks() {
        add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
        add_action('wp_ajax_speed_optimizer_network_settings', array($this, 'ajax_save_network_settings'));
        add_action('wp_ajax_speed_optimizer_bulk_optimize', array($this, 'ajax_bulk_optimize'));
        add_filter('speed_optimizer_network_settings', array($this, 'get_network_settings'));
    }
    
    /**
     * Add network admin menu
     */
    public function add_network_admin_menu() {
        add_menu_page(
            __('Speed Optimizer Network', 'speed-optimizer'),
            __('Speed Optimizer', 'speed-optimizer'),
            'manage_network_options',
            'speed-optimizer-network',
            array($this, 'network_admin_page'),
            'dashicons-performance',
            30
        );
        
        add_submenu_page(
            'speed-optimizer-network',
            __('Network Settings', 'speed-optimizer'),
            __('Settings', 'speed-optimizer'),
            'manage_network_options',
            'speed-optimizer-network-settings',
            array($this, 'network_settings_page')
        );
        
        add_submenu_page(
            'speed-optimizer-network',
            __('Site Management', 'speed-optimizer'),
            __('Sites', 'speed-optimizer'),
            'manage_network_options',
            'speed-optimizer-network-sites',
            array($this, 'network_sites_page')
        );
    }
    
    /**
     * Network admin dashboard
     */
    public function network_admin_page() {
        $sites = get_sites();
        $total_sites = count($sites);
        $optimized_sites = $this->get_optimized_sites_count();
        $performance_stats = $this->get_network_performance_stats();
        
        include SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/network-dashboard.php';
    }
    
    /**
     * Network settings page
     */
    public function network_settings_page() {
        $network_settings = $this->get_network_settings();
        
        if (isset($_POST['submit_network_settings']) && wp_verify_nonce($_POST['_wpnonce'], 'speed_optimizer_network_settings')) {
            $this->save_network_settings($_POST);
            echo '<div class="notice notice-success"><p>' . __('Network settings saved successfully!', 'speed-optimizer') . '</p></div>';
        }
        
        include SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/network-settings.php';
    }
    
    /**
     * Network sites management page
     */
    public function network_sites_page() {
        $sites = get_sites(array(
            'number' => 100,
            'orderby' => 'domain'
        ));
        
        include SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/network-sites.php';
    }
    
    /**
     * Get network settings
     */
    public function get_network_settings() {
        return array(
            'enforce_settings' => get_site_option('speed_optimizer_enforce_settings', 0),
            'allowed_optimizations' => get_site_option('speed_optimizer_allowed_optimizations', array()),
            'global_cdn_url' => get_site_option('speed_optimizer_global_cdn_url', ''),
            'global_api_key' => get_site_option('speed_optimizer_global_api_key', ''),
            'auto_optimize_new_sites' => get_site_option('speed_optimizer_auto_optimize_new_sites', 0),
            'performance_monitoring' => get_site_option('speed_optimizer_performance_monitoring', 1),
            'reporting_frequency' => get_site_option('speed_optimizer_reporting_frequency', 'weekly')
        );
    }
    
    /**
     * Save network settings
     */
    private function save_network_settings($data) {
        update_site_option('speed_optimizer_enforce_settings', intval($data['enforce_settings']));
        update_site_option('speed_optimizer_allowed_optimizations', $data['allowed_optimizations']);
        update_site_option('speed_optimizer_global_cdn_url', sanitize_url($data['global_cdn_url']));
        update_site_option('speed_optimizer_global_api_key', sanitize_text_field($data['global_api_key']));
        update_site_option('speed_optimizer_auto_optimize_new_sites', intval($data['auto_optimize_new_sites']));
        update_site_option('speed_optimizer_performance_monitoring', intval($data['performance_monitoring']));
        update_site_option('speed_optimizer_reporting_frequency', sanitize_text_field($data['reporting_frequency']));
    }
    
    /**
     * Get count of optimized sites
     */
    private function get_optimized_sites_count() {
        $sites = get_sites();
        $optimized_count = 0;
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            if (get_option('speed_optimizer_enable_caching', 0)) {
                $optimized_count++;
            }
            
            restore_current_blog();
        }
        
        return $optimized_count;
    }
    
    /**
     * Get network performance statistics
     */
    private function get_network_performance_stats() {
        global $wpdb;
        
        $stats = array(
            'total_tests' => 0,
            'avg_desktop_score' => 0,
            'avg_mobile_score' => 0,
            'sites_with_issues' => 0
        );
        
        $sites = get_sites();
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            $table_name = $wpdb->prefix . 'speed_optimizer_tests';
            
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") == $table_name) {
                $site_stats = $wpdb->get_row("
                    SELECT 
                        COUNT(*) as total_tests,
                        AVG(desktop_score) as avg_desktop,
                        AVG(mobile_score) as avg_mobile
                    FROM {$table_name}
                    WHERE created_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
                ");
                
                if ($site_stats) {
                    $stats['total_tests'] += $site_stats->total_tests;
                    $stats['avg_desktop_score'] += $site_stats->avg_desktop;
                    $stats['avg_mobile_score'] += $site_stats->avg_mobile;
                    
                    if ($site_stats->avg_desktop < 60 || $site_stats->avg_mobile < 60) {
                        $stats['sites_with_issues']++;
                    }
                }
            }
            
            restore_current_blog();
        }
        
        $site_count = count($sites);
        if ($site_count > 0) {
            $stats['avg_desktop_score'] = round($stats['avg_desktop_score'] / $site_count);
            $stats['avg_mobile_score'] = round($stats['avg_mobile_score'] / $site_count);
        }
        
        return $stats;
    }
    
    /**
     * Bulk optimize sites
     */
    public function bulk_optimize_sites($site_ids) {
        $results = array();
        
        foreach ($site_ids as $site_id) {
            switch_to_blog($site_id);
            
            $site_info = get_blog_details($site_id);
            
            try {
                // Enable basic optimizations
                update_option('speed_optimizer_enable_caching', 1);
                update_option('speed_optimizer_enable_minification', 1);
                update_option('speed_optimizer_enable_compression', 1);
                update_option('speed_optimizer_enable_image_optimization', 1);
                
                // Apply network settings if enforced
                $network_settings = $this->get_network_settings();
                if ($network_settings['enforce_settings']) {
                    if (!empty($network_settings['global_cdn_url'])) {
                        update_option('speed_optimizer_cdn_url', $network_settings['global_cdn_url']);
                    }
                    if (!empty($network_settings['global_api_key'])) {
                        update_option('speed_optimizer_pagespeed_api_key', $network_settings['global_api_key']);
                    }
                }
                
                // Run optimization
                $optimizer = new Speed_Optimizer_Optimizer();
                $optimization_result = $optimizer->optimize_database();
                
                $results[$site_id] = array(
                    'success' => true,
                    'site_name' => $site_info->blogname,
                    'message' => __('Optimization completed successfully', 'speed-optimizer')
                );
                
            } catch (Exception $e) {
                $results[$site_id] = array(
                    'success' => false,
                    'site_name' => $site_info->blogname,
                    'message' => $e->getMessage()
                );
            }
            
            restore_current_blog();
        }
        
        return $results;
    }
    
    /**
     * AJAX handler for saving network settings
     */
    public function ajax_save_network_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_network_options')) {
            wp_die('Insufficient permissions');
        }
        
        $this->save_network_settings($_POST);
        
        wp_send_json_success(array(
            'message' => __('Network settings saved successfully', 'speed-optimizer')
        ));
    }
    
    /**
     * AJAX handler for bulk optimization
     */
    public function ajax_bulk_optimize() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_network_options')) {
            wp_die('Insufficient permissions');
        }
        
        $site_ids = array_map('intval', $_POST['site_ids']);
        $results = $this->bulk_optimize_sites($site_ids);
        
        wp_send_json_success(array(
            'results' => $results,
            'message' => sprintf(__('Bulk optimization completed for %d sites', 'speed-optimizer'), count($site_ids))
        ));
    }
    
    /**
     * Check if site-level settings are enforced
     */
    public function are_settings_enforced() {
        if (!is_multisite()) {
            return false;
        }
        
        $network_settings = $this->get_network_settings();
        return $network_settings['enforce_settings'];
    }
    
    /**
     * Get allowed optimizations for sub-sites
     */
    public function get_allowed_optimizations() {
        if (!is_multisite()) {
            return array();
        }
        
        $network_settings = $this->get_network_settings();
        return $network_settings['allowed_optimizations'];
    }
    
    /**
     * Schedule network performance report
     */
    public function schedule_network_report() {
        if (!wp_next_scheduled('speed_optimizer_network_report')) {
            $frequency = get_site_option('speed_optimizer_reporting_frequency', 'weekly');
            wp_schedule_event(time(), $frequency, 'speed_optimizer_network_report');
        }
        
        add_action('speed_optimizer_network_report', array($this, 'send_network_report'));
    }
    
    /**
     * Send network performance report
     */
    public function send_network_report() {
        $stats = $this->get_network_performance_stats();
        $sites = get_sites();
        
        $email_content = $this->generate_network_report_email($stats, $sites);
        
        $network_admin_email = get_site_option('admin_email');
        $subject = sprintf(__('Speed Optimizer Network Report - %s', 'speed-optimizer'), get_network()->domain);
        
        wp_mail($network_admin_email, $subject, $email_content, array('Content-Type: text/html; charset=UTF-8'));
    }
    
    /**
     * Generate network report email content
     */
    private function generate_network_report_email($stats, $sites) {
        ob_start();
        ?>
        <h2><?php _e('Speed Optimizer Network Performance Report', 'speed-optimizer'); ?></h2>
        
        <h3><?php _e('Network Overview', 'speed-optimizer'); ?></h3>
        <ul>
            <li><?php printf(__('Total Sites: %d', 'speed-optimizer'), count($sites)); ?></li>
            <li><?php printf(__('Total Tests: %d', 'speed-optimizer'), $stats['total_tests']); ?></li>
            <li><?php printf(__('Average Desktop Score: %d', 'speed-optimizer'), $stats['avg_desktop_score']); ?></li>
            <li><?php printf(__('Average Mobile Score: %d', 'speed-optimizer'), $stats['avg_mobile_score']); ?></li>
            <li><?php printf(__('Sites with Performance Issues: %d', 'speed-optimizer'), $stats['sites_with_issues']); ?></li>
        </ul>
        
        <h3><?php _e('Recommendations', 'speed-optimizer'); ?></h3>
        <?php if ($stats['sites_with_issues'] > 0): ?>
            <p><?php _e('Some sites have performance scores below 60. Consider running bulk optimization or reviewing their settings.', 'speed-optimizer'); ?></p>
        <?php else: ?>
            <p><?php _e('All sites are performing well! Keep up the good work.', 'speed-optimizer'); ?></p>
        <?php endif; ?>
        
        <p>
            <a href="<?php echo network_admin_url('admin.php?page=speed-optimizer-network'); ?>">
                <?php _e('View Network Dashboard', 'speed-optimizer'); ?>
            </a>
        </p>
        <?php
        
        return ob_get_clean();
    }
}