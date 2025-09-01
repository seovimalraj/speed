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
            'enable_caching' => get_option('speed_optimizer_enable_caching', 1),
            'enable_minification' => get_option('speed_optimizer_enable_minification', 1),
            'enable_compression' => get_option('speed_optimizer_enable_compression', 1),
            'enable_image_optimization' => get_option('speed_optimizer_enable_image_optimization', 1),
            'enable_database_optimization' => get_option('speed_optimizer_enable_database_optimization', 0),
            'pagespeed_api_key' => get_option('speed_optimizer_pagespeed_api_key', ''),
            'optimization_level' => get_option('speed_optimizer_optimization_level', 'moderate'),
            'cdn_url' => get_option('speed_optimizer_cdn_url', ''),
            'exclude_files' => get_option('speed_optimizer_exclude_files', ''),
            'cache_expiration' => get_option('speed_optimizer_cache_expiration', 86400)
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
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/';
        if (is_dir($cache_dir)) {
            $this->delete_directory($cache_dir);
        }
        
        // Clear WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        $database = new Speed_Optimizer_Database();
        $database->log_action('cache_cleared', 'All cache files cleared');
        
        return true;
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