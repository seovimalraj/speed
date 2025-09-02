<?php
/**
 * White-labeling and custom branding functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_White_Label {
    
    private $license;
    private $branding_options;
    
    public function __construct() {
        $this->license = new Speed_Optimizer_License();
        
        if ($this->license->is_feature_available('white_labeling')) {
            $this->load_branding_options();
            $this->init_hooks();
        }
    }
    
    /**
     * Load branding options
     */
    private function load_branding_options() {
        $this->branding_options = array(
            'plugin_name' => get_option('speed_optimizer_white_label_plugin_name', 'Speed Optimizer'),
            'plugin_description' => get_option('speed_optimizer_white_label_plugin_description', ''),
            'company_name' => get_option('speed_optimizer_white_label_company_name', ''),
            'company_logo' => get_option('speed_optimizer_white_label_company_logo', ''),
            'company_url' => get_option('speed_optimizer_white_label_company_url', ''),
            'support_email' => get_option('speed_optimizer_white_label_support_email', ''),
            'support_url' => get_option('speed_optimizer_white_label_support_url', ''),
            'hide_original_branding' => get_option('speed_optimizer_white_label_hide_branding', 0),
            'custom_colors' => get_option('speed_optimizer_white_label_custom_colors', array()),
            'custom_css' => get_option('speed_optimizer_white_label_custom_css', ''),
            'footer_text' => get_option('speed_optimizer_white_label_footer_text', ''),
            'dashboard_widgets' => get_option('speed_optimizer_white_label_dashboard_widgets', array())
        );
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Admin menu customization
        add_filter('speed_optimizer_menu_title', array($this, 'customize_menu_title'));
        add_filter('speed_optimizer_page_title', array($this, 'customize_page_title'));
        
        // Branding customization
        add_action('admin_head', array($this, 'inject_custom_styles'));
        add_action('admin_footer', array($this, 'inject_custom_scripts'));
        
        // Plugin information filters
        add_filter('speed_optimizer_plugin_info', array($this, 'customize_plugin_info'));
        add_filter('speed_optimizer_support_info', array($this, 'customize_support_info'));
        
        // Hide original branding if enabled
        if ($this->branding_options['hide_original_branding']) {
            add_filter('speed_optimizer_show_original_branding', '__return_false');
        }
        
        // Custom dashboard widgets
        add_action('wp_dashboard_setup', array($this, 'add_custom_dashboard_widgets'));
        
        // AJAX handlers
        add_action('wp_ajax_speed_optimizer_save_white_label_settings', array($this, 'ajax_save_white_label_settings'));
        add_action('wp_ajax_speed_optimizer_upload_logo', array($this, 'ajax_upload_logo'));
    }
    
    /**
     * Customize menu title
     */
    public function customize_menu_title($title) {
        return !empty($this->branding_options['plugin_name']) ? $this->branding_options['plugin_name'] : $title;
    }
    
    /**
     * Customize page title
     */
    public function customize_page_title($title) {
        $plugin_name = !empty($this->branding_options['plugin_name']) ? $this->branding_options['plugin_name'] : 'Speed Optimizer';
        return str_replace('Speed Optimizer', $plugin_name, $title);
    }
    
    /**
     * Inject custom styles
     */
    public function inject_custom_styles() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'speed-optimizer') !== false) {
            ?>
            <style>
            <?php if (!empty($this->branding_options['custom_colors'])): ?>
                <?php $colors = $this->branding_options['custom_colors']; ?>
                
                <?php if (!empty($colors['primary'])): ?>
                .speed-optimizer-admin .button-primary,
                .speed-optimizer-admin .nav-tab-active {
                    background-color: <?php echo esc_attr($colors['primary']); ?> !important;
                    border-color: <?php echo esc_attr($colors['primary']); ?> !important;
                }
                <?php endif; ?>
                
                <?php if (!empty($colors['secondary'])): ?>
                .speed-optimizer-admin .button-secondary {
                    border-color: <?php echo esc_attr($colors['secondary']); ?> !important;
                    color: <?php echo esc_attr($colors['secondary']); ?> !important;
                }
                <?php endif; ?>
                
                <?php if (!empty($colors['accent'])): ?>
                .speed-optimizer-admin .license-status-card,
                .speed-optimizer-admin .upgrade-hero {
                    background: <?php echo esc_attr($colors['accent']); ?> !important;
                }
                <?php endif; ?>
            <?php endif; ?>
            
            <?php if (!empty($this->branding_options['custom_css'])): ?>
                <?php echo $this->branding_options['custom_css']; ?>
            <?php endif; ?>
            
            <?php if (!empty($this->branding_options['company_logo'])): ?>
                .speed-optimizer-admin .wrap h1:before {
                    content: '';
                    display: inline-block;
                    width: 32px;
                    height: 32px;
                    background: url('<?php echo esc_url($this->branding_options['company_logo']); ?>') no-repeat center;
                    background-size: contain;
                    vertical-align: middle;
                    margin-right: 10px;
                }
            <?php endif; ?>
            </style>
            <?php
        }
    }
    
    /**
     * Inject custom scripts
     */
    public function inject_custom_scripts() {
        $screen = get_current_screen();
        
        if (strpos($screen->id, 'speed-optimizer') !== false) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                // Replace footer text if customized
                <?php if (!empty($this->branding_options['footer_text'])): ?>
                $('#wpfooter').html('<?php echo esc_js($this->branding_options['footer_text']); ?>');
                <?php endif; ?>
                
                // Replace support links
                <?php if (!empty($this->branding_options['support_url'])): ?>
                $('a[href*="support"]').attr('href', '<?php echo esc_url($this->branding_options['support_url']); ?>');
                <?php endif; ?>
            });
            </script>
            <?php
        }
    }
    
    /**
     * Customize plugin information
     */
    public function customize_plugin_info($info) {
        $customized_info = $info;
        
        if (!empty($this->branding_options['plugin_name'])) {
            $customized_info['name'] = $this->branding_options['plugin_name'];
        }
        
        if (!empty($this->branding_options['plugin_description'])) {
            $customized_info['description'] = $this->branding_options['plugin_description'];
        }
        
        if (!empty($this->branding_options['company_name'])) {
            $customized_info['author'] = $this->branding_options['company_name'];
        }
        
        if (!empty($this->branding_options['company_url'])) {
            $customized_info['author_url'] = $this->branding_options['company_url'];
        }
        
        return $customized_info;
    }
    
    /**
     * Customize support information
     */
    public function customize_support_info($info) {
        $customized_info = $info;
        
        if (!empty($this->branding_options['support_email'])) {
            $customized_info['email'] = $this->branding_options['support_email'];
        }
        
        if (!empty($this->branding_options['support_url'])) {
            $customized_info['url'] = $this->branding_options['support_url'];
        }
        
        return $customized_info;
    }
    
    /**
     * Add custom dashboard widgets
     */
    public function add_custom_dashboard_widgets() {
        $widgets = $this->branding_options['dashboard_widgets'];
        
        foreach ($widgets as $widget) {
            if (!empty($widget['title']) && !empty($widget['content'])) {
                wp_add_dashboard_widget(
                    'speed_optimizer_custom_widget_' . md5($widget['title']),
                    $widget['title'],
                    function() use ($widget) {
                        echo wp_kses_post($widget['content']);
                    }
                );
            }
        }
    }
    
    /**
     * Get white label settings for admin
     */
    public function get_white_label_settings() {
        return array(
            'plugin_name' => $this->branding_options['plugin_name'],
            'plugin_description' => $this->branding_options['plugin_description'],
            'company_name' => $this->branding_options['company_name'],
            'company_logo' => $this->branding_options['company_logo'],
            'company_url' => $this->branding_options['company_url'],
            'support_email' => $this->branding_options['support_email'],
            'support_url' => $this->branding_options['support_url'],
            'hide_original_branding' => $this->branding_options['hide_original_branding'],
            'custom_colors' => $this->branding_options['custom_colors'],
            'custom_css' => $this->branding_options['custom_css'],
            'footer_text' => $this->branding_options['footer_text']
        );
    }
    
    /**
     * Save white label settings
     */
    public function save_white_label_settings($settings) {
        update_option('speed_optimizer_white_label_plugin_name', sanitize_text_field($settings['plugin_name']));
        update_option('speed_optimizer_white_label_plugin_description', sanitize_textarea_field($settings['plugin_description']));
        update_option('speed_optimizer_white_label_company_name', sanitize_text_field($settings['company_name']));
        update_option('speed_optimizer_white_label_company_logo', esc_url_raw($settings['company_logo']));
        update_option('speed_optimizer_white_label_company_url', esc_url_raw($settings['company_url']));
        update_option('speed_optimizer_white_label_support_email', sanitize_email($settings['support_email']));
        update_option('speed_optimizer_white_label_support_url', esc_url_raw($settings['support_url']));
        update_option('speed_optimizer_white_label_hide_branding', intval($settings['hide_original_branding']));
        update_option('speed_optimizer_white_label_custom_colors', $settings['custom_colors']);
        update_option('speed_optimizer_white_label_custom_css', wp_strip_all_tags($settings['custom_css']));
        update_option('speed_optimizer_white_label_footer_text', sanitize_text_field($settings['footer_text']));
        
        // Reload options
        $this->load_branding_options();
    }
    
    /**
     * Generate client report
     */
    public function generate_client_report($site_url, $timeframe = '30_days') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_tests';
        
        switch ($timeframe) {
            case '7_days':
                $date_condition = "DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '90_days':
                $date_condition = "DATE_SUB(NOW(), INTERVAL 90 DAY)";
                break;
            default:
                $date_condition = "DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
        
        $tests = $wpdb->get_results($wpdb->prepare("
            SELECT *
            FROM {$table_name}
            WHERE url = %s
            AND created_at > {$date_condition}
            ORDER BY created_at DESC
        ", $site_url));
        
        $report_data = array(
            'site_url' => $site_url,
            'timeframe' => $timeframe,
            'total_tests' => count($tests),
            'avg_desktop_score' => 0,
            'avg_mobile_score' => 0,
            'improvements' => array(),
            'recommendations' => array(),
            'branding' => $this->branding_options
        );
        
        if (!empty($tests)) {
            $desktop_scores = array_column($tests, 'desktop_score');
            $mobile_scores = array_column($tests, 'mobile_score');
            
            $report_data['avg_desktop_score'] = round(array_sum($desktop_scores) / count($desktop_scores));
            $report_data['avg_mobile_score'] = round(array_sum($mobile_scores) / count($mobile_scores));
            
            // Calculate improvements
            $first_test = end($tests);
            $latest_test = reset($tests);
            
            $report_data['improvements'] = array(
                'desktop_improvement' => $latest_test->desktop_score - $first_test->desktop_score,
                'mobile_improvement' => $latest_test->mobile_score - $first_test->mobile_score
            );
        }
        
        return $report_data;
    }
    
    /**
     * Export client report as PDF
     */
    public function export_client_report_pdf($report_data) {
        // This would typically use a PDF library like TCPDF or DOMPDF
        // For now, we'll return the HTML content that could be converted to PDF
        
        ob_start();
        include SPEED_OPTIMIZER_PLUGIN_DIR . 'admin/templates/client-report.php';
        $html_content = ob_get_clean();
        
        return $html_content;
    }
    
    /**
     * AJAX handler for saving white label settings
     */
    public function ajax_save_white_label_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        if (!$this->license->is_feature_available('white_labeling')) {
            wp_send_json_error('White labeling requires an Agency license');
        }
        
        $settings = array(
            'plugin_name' => $_POST['plugin_name'],
            'plugin_description' => $_POST['plugin_description'],
            'company_name' => $_POST['company_name'],
            'company_logo' => $_POST['company_logo'],
            'company_url' => $_POST['company_url'],
            'support_email' => $_POST['support_email'],
            'support_url' => $_POST['support_url'],
            'hide_original_branding' => intval($_POST['hide_original_branding']),
            'custom_colors' => $_POST['custom_colors'],
            'custom_css' => $_POST['custom_css'],
            'footer_text' => $_POST['footer_text']
        );
        
        $this->save_white_label_settings($settings);
        
        wp_send_json_success(array(
            'message' => __('White label settings saved successfully', 'speed-optimizer')
        ));
    }
    
    /**
     * AJAX handler for logo upload
     */
    public function ajax_upload_logo() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($_FILES['logo_file'], $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array(
                'url' => $movefile['url'],
                'message' => __('Logo uploaded successfully', 'speed-optimizer')
            ));
        } else {
            wp_send_json_error($movefile['error']);
        }
    }
    
    /**
     * Check if white labeling is enabled
     */
    public function is_white_labeling_enabled() {
        return $this->license->is_feature_available('white_labeling');
    }
    
    /**
     * Get branded plugin name
     */
    public function get_plugin_name() {
        return !empty($this->branding_options['plugin_name']) ? $this->branding_options['plugin_name'] : 'Speed Optimizer';
    }
    
    /**
     * Get company branding info
     */
    public function get_company_info() {
        return array(
            'name' => $this->branding_options['company_name'],
            'logo' => $this->branding_options['company_logo'],
            'url' => $this->branding_options['company_url'],
            'support_email' => $this->branding_options['support_email'],
            'support_url' => $this->branding_options['support_url']
        );
    }
}