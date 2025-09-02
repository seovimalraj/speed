<?php
/**
 * Plugin Name: Speed Nav
 * Plugin URI: https://narvab.com
 * Description: WordPress speed optimization plugin with PageSpeed Insights integration and personalized configuration options.
 * Version: 1.0.0
 * Author: Vimal Raj
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: speed-optimizer
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SPEED_OPTIMIZER_VERSION', '1.0.0');
define('SPEED_OPTIMIZER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SPEED_OPTIMIZER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('SPEED_OPTIMIZER_PLUGIN_FILE', __FILE__);

/**
 * Main plugin class
 */
class SpeedOptimizer {
    
    /**
     * Instance of this class
     */
    private static $instance = null;
    
    /**
     * Get instance of this class
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize the plugin
     */
    private function init() {
        // Load required files
        $this->load_dependencies();
        
        // Hook into WordPress
        add_action('init', array($this, 'init_plugin'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Plugin activation and deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-database.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-pagespeed-api.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-optimizer.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-license.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-fastspring.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-premium.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-multisite.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'includes/class-white-label.php';
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/class-admin.php';
    }
    
    /**
     * Initialize plugin after WordPress is loaded
     */
    public function init_plugin() {
        // Load text domain for translations
        load_plugin_textdomain('speed-optimizer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Speed Optimizer', 'speed-optimizer'),
            __('Speed Optimizer', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer',
            array($this, 'admin_page'),
            'dashicons-performance',
            30
        );
        
        add_submenu_page(
            'speed-optimizer',
            __('Dashboard', 'speed-optimizer'),
            __('Dashboard', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'speed-optimizer',
            __('Speed Test', 'speed-optimizer'),
            __('Speed Test', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer-test',
            array($this, 'speed_test_page')
        );
        
        add_submenu_page(
            'speed-optimizer',
            __('Settings', 'speed-optimizer'),
            __('Settings', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer-settings',
            array($this, 'settings_page')
        );
        
        add_submenu_page(
            'speed-optimizer',
            __('License', 'speed-optimizer'),
            __('License', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer-license',
            array($this, 'license_page')
        );
        
        add_submenu_page(
            'speed-optimizer',
            __('Upgrade', 'speed-optimizer'),
            __('Upgrade', 'speed-optimizer'),
            'manage_options',
            'speed-optimizer-upgrade',
            array($this, 'upgrade_page')
        );
        
        // Add white-label menu for agency users
        $license = new Speed_Optimizer_License();
        if ($license->is_feature_available('white_labeling')) {
            add_submenu_page(
                'speed-optimizer',
                __('White Label', 'speed-optimizer'),
                __('White Label', 'speed-optimizer'),
                'manage_options',
                'speed-optimizer-white-label',
                array($this, 'white_label_page')
            );
        }
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'speed-optimizer') !== false) {
            wp_enqueue_style(
                'speed-optimizer-admin',
                SPEED_OPTIMIZER_PLUGIN_URL . 'assets/css/admin.css',
                array(),
                SPEED_OPTIMIZER_VERSION
            );
            
            wp_enqueue_script(
                'speed-optimizer-admin',
                SPEED_OPTIMIZER_PLUGIN_URL . 'assets/js/admin.js',
                array('jquery'),
                SPEED_OPTIMIZER_VERSION,
                true
            );
            
            wp_localize_script(
                'speed-optimizer-admin',
                'speedOptimizer',
                array(
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('speed_optimizer_nonce'),
                    'strings' => array(
                        'testing' => __('Testing...', 'speed-optimizer'),
                        'error' => __('Error occurred', 'speed-optimizer'),
                        'success' => __('Test completed', 'speed-optimizer')
                    )
                )
            );
        }
    }
    
    /**
     * Admin dashboard page
     */
    public function admin_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/dashboard.php';
    }
    
    /**
     * Speed test page
     */
    public function speed_test_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/speed-test.php';
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/settings.php';
    }
    
    /**
     * License page
     */
    public function license_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/license.php';
    }
    
    /**
     * Upgrade page
     */
    public function upgrade_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/upgrade.php';
    }
    
    /**
     * White label page
     */
    public function white_label_page() {
        require_once SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/white-label.php';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create database tables
        $database = new Speed_Optimizer_Database();
        $database->create_tables();
        Speed_Optimizer_FastSpring::create_license_table();
        
        // Set default options
        $this->set_default_options();
        
        // Initialize premium features
        new Speed_Optimizer_Premium();
        
        // Initialize multisite support
        new Speed_Optimizer_Multisite();
        
        // Initialize white labeling
        new Speed_Optimizer_White_Label();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'enable_caching' => 1,
            'enable_minification' => 1,
            'enable_compression' => 1,
            'pagespeed_api_key' => '',
            'optimization_level' => 'moderate',
            'enable_image_optimization' => 1,
            'enable_database_optimization' => 0,
            'cdn_url' => '',
            'exclude_files' => '',
            'cache_expiration' => 86400
        );
        
        foreach ($default_options as $option => $value) {
            if (!get_option('speed_optimizer_' . $option)) {
                add_option('speed_optimizer_' . $option, $value);
            }
        }
    }
}

// Initialize the plugin
SpeedOptimizer::get_instance();

// AJAX handlers
add_action('wp_ajax_speed_optimizer_test', 'speed_optimizer_ajax_test');
add_action('wp_ajax_speed_optimizer_save_settings', 'speed_optimizer_ajax_save_settings');
add_action('wp_ajax_speed_optimizer_clear_cache', 'speed_optimizer_ajax_clear_cache');
add_action('wp_ajax_speed_optimizer_test_api_key', 'speed_optimizer_ajax_test_api_key');
add_action('wp_ajax_speed_optimizer_export_settings', 'speed_optimizer_ajax_export_settings');
add_action('wp_ajax_speed_optimizer_import_settings', 'speed_optimizer_ajax_import_settings');
add_action('wp_ajax_speed_optimizer_get_test_details', 'speed_optimizer_ajax_get_test_details');

// License and premium AJAX handlers
add_action('wp_ajax_speed_optimizer_activate_license', 'speed_optimizer_ajax_activate_license');
add_action('wp_ajax_speed_optimizer_deactivate_license', 'speed_optimizer_ajax_deactivate_license');
add_action('wp_ajax_speed_optimizer_check_license', 'speed_optimizer_ajax_check_license');
add_action('wp_ajax_speed_optimizer_create_checkout', 'speed_optimizer_ajax_create_checkout');
add_action('wp_ajax_speed_optimizer_generate_critical_css', 'speed_optimizer_ajax_generate_critical_css');
add_action('wp_ajax_speed_optimizer_clear_fragment_cache', 'speed_optimizer_ajax_clear_fragment_cache');

/**
 * AJAX handler for speed test
 */
function speed_optimizer_ajax_test() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $url = esc_url_raw($_POST['url']);
    $api = new Speed_Optimizer_PageSpeed_API();
    $result = $api->test_url($url);
    
    wp_send_json($result);
}

/**
 * AJAX handler for saving settings
 */
function speed_optimizer_ajax_save_settings() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $settings = $_POST['settings'];
    $saved = array();
    
    // Save each setting with proper sanitization
    foreach ($settings as $key => $value) {
        $option_name = 'speed_optimizer_' . sanitize_key($key);
        $sanitized_value = sanitize_text_field($value);
        update_option($option_name, $sanitized_value);
        $saved[$key] = $sanitized_value;
    }
    
    wp_send_json_success($saved);
}

/**
 * AJAX handler for clearing cache
 */
function speed_optimizer_ajax_clear_cache() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $admin = new Speed_Optimizer_Admin();
    $result = $admin->clear_cache();
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

/**
 * AJAX handler for testing API key
 */
function speed_optimizer_ajax_test_api_key() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $api_key = sanitize_text_field($_POST['api_key']);
    $api = new Speed_Optimizer_PageSpeed_API();
    $is_valid = $api->validate_api_key($api_key);
    
    if ($is_valid) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

/**
 * AJAX handler for exporting settings
 */
function speed_optimizer_ajax_export_settings() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $admin = new Speed_Optimizer_Admin();
    $export_data = $admin->export_settings();
    
    wp_send_json_success($export_data);
}

/**
 * AJAX handler for importing settings
 */
function speed_optimizer_ajax_import_settings() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    $settings_data = $_POST['settings_data'];
    $admin = new Speed_Optimizer_Admin();
    $imported = $admin->import_settings($settings_data);
    
    if ($imported) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}

/**
 * AJAX handler for getting test details
 */
function speed_optimizer_ajax_get_test_details() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
        wp_die('Security check failed');
    }
    
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Insufficient permissions');
    }
    
    global $wpdb;
    $test_id = intval($_POST['test_id']);
    $table_name = $wpdb->prefix . 'speed_optimizer_tests';
    
    $test = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE id = %d",
        $test_id
    ));
    
    if (!$test) {
        wp_send_json_error('Test not found');
    }
    
    $raw_data = json_decode($test->raw_data, true);
    
    $html = '<div class="test-details">';
    $html .= '<h4>Test Results for: ' . esc_html($test->url) . '</h4>';
    $html .= '<p><strong>Date:</strong> ' . date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($test->test_date)) . '</p>';
    
    if ($raw_data && isset($raw_data['desktop'])) {
        $html .= '<h5>Desktop Results</h5>';
        $html .= '<p><strong>Score:</strong> <span class="score-badge score-' . Speed_Optimizer_PageSpeed_API::get_score_color($test->desktop_score) . '">' . $test->desktop_score . '</span></p>';
        if (isset($raw_data['desktop']['metrics'])) {
            $html .= '<ul>';
            foreach ($raw_data['desktop']['metrics'] as $metric => $value) {
                $html .= '<li><strong>' . strtoupper($metric) . ':</strong> ' . $value . '</li>';
            }
            $html .= '</ul>';
        }
    }
    
    if ($raw_data && isset($raw_data['mobile'])) {
        $html .= '<h5>Mobile Results</h5>';
        $html .= '<p><strong>Score:</strong> <span class="score-badge score-' . Speed_Optimizer_PageSpeed_API::get_score_color($test->mobile_score) . '">' . $test->mobile_score . '</span></p>';
        if (isset($raw_data['mobile']['metrics'])) {
            $html .= '<ul>';
            foreach ($raw_data['mobile']['metrics'] as $metric => $value) {
                $html .= '<li><strong>' . strtoupper($metric) . ':</strong> ' . $value . '</li>';
            }
            $html .= '</ul>';
        }
    }
    
    $html .= '</div>';
    
    wp_send_json_success($html);
}
