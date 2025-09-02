<?php
/**
 * Admin interface class
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_Admin {
    
    public function __construct() {
        add_action('admin_init', array($this, 'init_settings'));
        add_action('wp_ajax_run_database_optimization', array($this, 'ajax_database_optimization'));
    }
    
    /**
     * Initialize settings
     */
    public function init_settings() {
        // Basic settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_caching');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_minification');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_compression');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_image_optimization');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_database_optimization');
        register_setting('speed_optimizer_settings', 'speed_optimizer_pagespeed_api_key');
        register_setting('speed_optimizer_settings', 'speed_optimizer_optimization_level');
        register_setting('speed_optimizer_settings', 'speed_optimizer_cdn_url');
        register_setting('speed_optimizer_settings', 'speed_optimizer_exclude_files');
        register_setting('speed_optimizer_settings', 'speed_optimizer_cache_expiration');
        
        // New caching settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_page_caching');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_cache_preloading');
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_mobile_cache');
        register_setting('speed_optimizer_settings', 'speed_optimizer_cache_logged_users');
        register_setting('speed_optimizer_settings', 'speed_optimizer_cache_query_strings');
        register_setting('speed_optimizer_settings', 'speed_optimizer_user_agent_cache');
        
        // File optimization settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_enable_concatenation');
        register_setting('speed_optimizer_settings', 'speed_optimizer_remove_unused_css');
        register_setting('speed_optimizer_settings', 'speed_optimizer_delay_js_execution');
        register_setting('speed_optimizer_settings', 'speed_optimizer_defer_js_loading');
        register_setting('speed_optimizer_settings', 'speed_optimizer_inline_critical_css');
        
        // Media optimization settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_webp_conversion');
        register_setting('speed_optimizer_settings', 'speed_optimizer_add_image_dimensions');
        register_setting('speed_optimizer_settings', 'speed_optimizer_disable_emoji_script');
        
        // eCommerce settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_woocommerce_optimization');
        register_setting('speed_optimizer_settings', 'speed_optimizer_exclude_cart_checkout');
        register_setting('speed_optimizer_settings', 'speed_optimizer_refresh_cart_fragments');
        
        // Tools and utilities settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_heartbeat_control');
        register_setting('speed_optimizer_settings', 'speed_optimizer_heartbeat_frequency');
        register_setting('speed_optimizer_settings', 'speed_optimizer_preload_links');
        register_setting('speed_optimizer_settings', 'speed_optimizer_preload_fonts');
        register_setting('speed_optimizer_settings', 'speed_optimizer_preconnect_domains');
        
        // Advanced settings
        register_setting('speed_optimizer_settings', 'speed_optimizer_advanced_cache_rules');
    }
    
    /**
     * Get dashboard data
     */
    public function get_dashboard_data() {
        $database = new Speed_Optimizer_Database();
        $stats = $database->get_statistics();
        $recent_tests = $database->get_speed_test_history('', 5);
        $recent_logs = $database->get_logs(10);
        
        return array(
            'stats' => $stats,
            'recent_tests' => $recent_tests,
            'recent_logs' => $recent_logs,
            'site_url' => home_url(),
            'has_api_key' => !empty(get_option('speed_optimizer_pagespeed_api_key'))
        );
    }
    
    /**
     * Get current settings
     */
    public function get_settings() {
        return array(
            // Basic settings
            'enable_caching' => get_option('speed_optimizer_enable_caching', 1),
            'enable_minification' => get_option('speed_optimizer_enable_minification', 1),
            'enable_compression' => get_option('speed_optimizer_enable_compression', 1),
            'enable_image_optimization' => get_option('speed_optimizer_enable_image_optimization', 1),
            'enable_database_optimization' => get_option('speed_optimizer_enable_database_optimization', 0),
            'pagespeed_api_key' => get_option('speed_optimizer_pagespeed_api_key', ''),
            'optimization_level' => get_option('speed_optimizer_optimization_level', 'moderate'),
            'cdn_url' => get_option('speed_optimizer_cdn_url', ''),
            'exclude_files' => get_option('speed_optimizer_exclude_files', ''),
            'cache_expiration' => get_option('speed_optimizer_cache_expiration', 86400),
            
            // New caching settings
            'enable_page_caching' => get_option('speed_optimizer_enable_page_caching', 1),
            'enable_cache_preloading' => get_option('speed_optimizer_enable_cache_preloading', 0),
            'enable_mobile_cache' => get_option('speed_optimizer_enable_mobile_cache', 1),
            'cache_logged_users' => get_option('speed_optimizer_cache_logged_users', 0),
            'cache_query_strings' => get_option('speed_optimizer_cache_query_strings', 0),
            'user_agent_cache' => get_option('speed_optimizer_user_agent_cache', 0),
            
            // File optimization settings
            'enable_concatenation' => get_option('speed_optimizer_enable_concatenation', 0),
            'remove_unused_css' => get_option('speed_optimizer_remove_unused_css', 0),
            'delay_js_execution' => get_option('speed_optimizer_delay_js_execution', 0),
            'defer_js_loading' => get_option('speed_optimizer_defer_js_loading', 0),
            'inline_critical_css' => get_option('speed_optimizer_inline_critical_css', 0),
            
            // Media optimization settings
            'webp_conversion' => get_option('speed_optimizer_webp_conversion', 0),
            'add_image_dimensions' => get_option('speed_optimizer_add_image_dimensions', 1),
            'disable_emoji_script' => get_option('speed_optimizer_disable_emoji_script', 1),
            
            // eCommerce settings
            'woocommerce_optimization' => get_option('speed_optimizer_woocommerce_optimization', 1),
            'exclude_cart_checkout' => get_option('speed_optimizer_exclude_cart_checkout', 1),
            'refresh_cart_fragments' => get_option('speed_optimizer_refresh_cart_fragments', 1),
            
            // Tools and utilities settings
            'heartbeat_control' => get_option('speed_optimizer_heartbeat_control', 0),
            'heartbeat_frequency' => get_option('speed_optimizer_heartbeat_frequency', 60),
            'preload_links' => get_option('speed_optimizer_preload_links', 0),
            'preload_fonts' => get_option('speed_optimizer_preload_fonts', 0),
            'preconnect_domains' => get_option('speed_optimizer_preconnect_domains', ''),
            
            // Advanced settings
            'advanced_cache_rules' => get_option('speed_optimizer_advanced_cache_rules', '')
        );
    }
    
    /**
     * AJAX handler for database optimization
     */
    public function ajax_database_optimization() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        $optimizer = new Speed_Optimizer_Optimizer();
        $result = $optimizer->optimize_database();
        
        wp_send_json_success($result);
    }
    
    /**
     * Clear cache
     */
    public function clear_cache() {
        $optimizer = new Speed_Optimizer_Optimizer();
        return $optimizer->clear_all_caches();
    }
    
    /**
     * Recursively delete directory
     */
    private function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Export settings
     */
    public function export_settings() {
        $settings = $this->get_settings();
        $export_data = array(
            'version' => SPEED_OPTIMIZER_VERSION,
            'timestamp' => current_time('mysql'),
            'settings' => $settings
        );
        
        return json_encode($export_data, JSON_PRETTY_PRINT);
    }
    
    /**
     * Import settings
     */
    public function import_settings($json_data) {
        $data = json_decode($json_data, true);
        
        if (!$data || !isset($data['settings'])) {
            return false;
        }
        
        $settings = $data['settings'];
        $imported = 0;
        
        foreach ($settings as $key => $value) {
            $option_name = 'speed_optimizer_' . $key;
            update_option($option_name, sanitize_text_field($value));
            $imported++;
        }
        
        $database = new Speed_Optimizer_Database();
        $database->log_action('settings_imported', "Imported {$imported} settings");
        
        return $imported;
    }
}