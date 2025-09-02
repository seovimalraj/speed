<?php
/**
 * Speed optimization functionality
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_Optimizer {
    
    private $options;
    private $license;
    
    public function __construct() {
        $this->license = new Speed_Optimizer_License();
        $this->load_options();
        $this->init_hooks();
    }
    
    /**
     * Load plugin options
     */
    private function load_options() {
        $this->options = array(
            'enable_caching' => get_option('speed_optimizer_enable_caching', 1),
            'enable_minification' => get_option('speed_optimizer_enable_minification', 1),
            'enable_compression' => get_option('speed_optimizer_enable_compression', 1),
            'enable_image_optimization' => get_option('speed_optimizer_enable_image_optimization', 1),
            'enable_database_optimization' => get_option('speed_optimizer_enable_database_optimization', 0),
            'optimization_level' => get_option('speed_optimizer_optimization_level', 'moderate'),
            'cdn_url' => get_option('speed_optimizer_cdn_url', ''),
            'exclude_files' => get_option('speed_optimizer_exclude_files', ''),
            'cache_expiration' => get_option('speed_optimizer_cache_expiration', 86400),
            
            // New caching options
            'enable_page_caching' => get_option('speed_optimizer_enable_page_caching', 1),
            'enable_cache_preloading' => get_option('speed_optimizer_enable_cache_preloading', 0),
            'enable_mobile_cache' => get_option('speed_optimizer_enable_mobile_cache', 1),
            'cache_logged_users' => get_option('speed_optimizer_cache_logged_users', 0),
            
            // File optimization options
            'enable_concatenation' => get_option('speed_optimizer_enable_concatenation', 0),
            'remove_unused_css' => get_option('speed_optimizer_remove_unused_css', 0),
            'delay_js_execution' => get_option('speed_optimizer_delay_js_execution', 0),
            'defer_js_loading' => get_option('speed_optimizer_defer_js_loading', 0),
            'inline_critical_css' => get_option('speed_optimizer_inline_critical_css', 0),
            
            // Media optimization options
            'webp_conversion' => get_option('speed_optimizer_webp_conversion', 0),
            'add_image_dimensions' => get_option('speed_optimizer_add_image_dimensions', 1),
            'disable_emoji_script' => get_option('speed_optimizer_disable_emoji_script', 1),
            
            // eCommerce options
            'woocommerce_optimization' => get_option('speed_optimizer_woocommerce_optimization', 1),
            'exclude_cart_checkout' => get_option('speed_optimizer_exclude_cart_checkout', 1),
            'refresh_cart_fragments' => get_option('speed_optimizer_refresh_cart_fragments', 1),
            
            // Tools and utilities
            'heartbeat_control' => get_option('speed_optimizer_heartbeat_control', 0),
            'heartbeat_frequency' => get_option('speed_optimizer_heartbeat_frequency', 60),
            'preload_links' => get_option('speed_optimizer_preload_links', 0),
            'preload_fonts' => get_option('speed_optimizer_preload_fonts', 0),
            'preconnect_domains' => get_option('speed_optimizer_preconnect_domains', ''),
            
            // Advanced caching
            'cache_query_strings' => get_option('speed_optimizer_cache_query_strings', 0),
            'user_agent_cache' => get_option('speed_optimizer_user_agent_cache', 0),
            'advanced_cache_rules' => get_option('speed_optimizer_advanced_cache_rules', '')
        );
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Page caching
        if ($this->options['enable_page_caching'] && $this->license->is_feature_available('page_caching')) {
            add_action('init', array($this, 'setup_page_caching'));
        }
        
        // Basic features available to all tiers
        if ($this->options['enable_minification'] && $this->license->is_feature_available('basic_minification')) {
            add_action('wp_enqueue_scripts', array($this, 'minify_scripts'), 999);
        }
        
        // File concatenation
        if ($this->options['enable_concatenation'] && $this->license->is_feature_available('file_concatenation')) {
            add_action('wp_enqueue_scripts', array($this, 'concatenate_files'), 1000);
        }
        
        if ($this->options['enable_compression'] && $this->license->is_feature_available('gzip_compression')) {
            add_action('init', array($this, 'enable_gzip_compression'));
        }
        
        if ($this->options['enable_caching'] && $this->license->is_feature_available('basic_caching')) {
            add_action('init', array($this, 'setup_caching'));
        }
        
        // Image optimization
        if ($this->options['enable_image_optimization'] && $this->license->is_feature_available('basic_lazy_loading')) {
            add_filter('wp_get_attachment_image_src', array($this, 'optimize_image_src'), 10, 4);
            add_filter('the_content', array($this, 'add_lazy_loading_to_content'));
        }
        
        // WebP conversion
        if ($this->options['webp_conversion'] && $this->license->is_feature_available('webp_conversion')) {
            add_filter('wp_get_attachment_image_src', array($this, 'convert_to_webp'), 10, 4);
        }
        
        // Premium CDN feature
        if (!empty($this->options['cdn_url']) && $this->license->is_feature_available('cdn_integration')) {
            add_filter('wp_get_attachment_url', array($this, 'replace_with_cdn'));
        }
        
        // Disable WordPress emoji script
        if ($this->options['disable_emoji_script']) {
            add_action('init', array($this, 'disable_emoji_scripts'));
        }
        
        // WooCommerce optimizations
        if ($this->options['woocommerce_optimization'] && class_exists('WooCommerce')) {
            add_action('init', array($this, 'setup_woocommerce_optimizations'));
        }
        
        // Heartbeat control
        if ($this->options['heartbeat_control'] && $this->license->is_feature_available('heartbeat_control')) {
            add_action('init', array($this, 'control_heartbeat'));
        }
        
        // Cache preloading
        if ($this->options['enable_cache_preloading'] && $this->license->is_feature_available('cache_preloading')) {
            add_action('save_post', array($this, 'preload_cache_on_update'));
            add_action('wp_update_nav_menu', array($this, 'preload_cache_on_update'));
        }
        
        // General optimization hooks
        add_action('wp_head', array($this, 'add_performance_headers'), 1);
        add_action('wp_footer', array($this, 'add_lazy_loading_script'), 999);
        add_filter('script_loader_tag', array($this, 'add_async_defer_attributes'), 10, 2);
        add_filter('style_loader_tag', array($this, 'optimize_css_delivery'), 10, 2);
        
        // Advanced script optimization
        if ($this->options['delay_js_execution'] && $this->license->is_feature_available('delay_js_execution')) {
            add_filter('script_loader_tag', array($this, 'delay_javascript_execution'), 10, 2);
        }
        
        if ($this->options['defer_js_loading'] && $this->license->is_feature_available('defer_js_loading')) {
            add_filter('script_loader_tag', array($this, 'defer_javascript_loading'), 10, 2);
        }
    }
    
    /**
     * Minify CSS and JS files
     */
    public function minify_scripts() {
        if (is_admin()) {
            return;
        }
        
        global $wp_scripts, $wp_styles;
        
        // Minify CSS
        if ($wp_styles && $this->should_minify_css()) {
            foreach ($wp_styles->queue as $handle) {
                $style = $wp_styles->registered[$handle];
                if ($style && $this->is_local_file($style->src)) {
                    $minified_src = $this->minify_css_file($style->src);
                    if ($minified_src) {
                        $wp_styles->registered[$handle]->src = $minified_src;
                    }
                }
            }
        }
        
        // Minify JS
        if ($wp_scripts && $this->should_minify_js()) {
            foreach ($wp_scripts->queue as $handle) {
                $script = $wp_scripts->registered[$handle];
                if ($script && $this->is_local_file($script->src)) {
                    $minified_src = $this->minify_js_file($script->src);
                    if ($minified_src) {
                        $wp_scripts->registered[$handle]->src = $minified_src;
                    }
                }
            }
        }
    }
    
    /**
     * Check if CSS minification should be applied
     */
    private function should_minify_css() {
        return $this->options['optimization_level'] !== 'none';
    }
    
    /**
     * Check if JS minification should be applied
     */
    private function should_minify_js() {
        return $this->options['optimization_level'] === 'aggressive';
    }
    
    /**
     * Check if file is local
     */
    private function is_local_file($src) {
        if (empty($src)) {
            return false;
        }
        
        $home_url = home_url();
        return strpos($src, $home_url) === 0 || strpos($src, '/') === 0;
    }
    
    /**
     * Minify CSS file
     */
    private function minify_css_file($src) {
        $file_path = $this->get_local_file_path($src);
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/css/';
        if (!wp_mkdir_p($cache_dir)) {
            return false;
        }
        
        $file_hash = md5($file_path . filemtime($file_path));
        $cache_file = $cache_dir . $file_hash . '.css';
        $cache_url = content_url('cache/speed-optimizer/css/' . $file_hash . '.css');
        
        if (!file_exists($cache_file)) {
            $content = file_get_contents($file_path);
            $minified_content = $this->minify_css_content($content);
            file_put_contents($cache_file, $minified_content);
        }
        
        return $cache_url;
    }
    
    /**
     * Minify JS file
     */
    private function minify_js_file($src) {
        $file_path = $this->get_local_file_path($src);
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/js/';
        if (!wp_mkdir_p($cache_dir)) {
            return false;
        }
        
        $file_hash = md5($file_path . filemtime($file_path));
        $cache_file = $cache_dir . $file_hash . '.js';
        $cache_url = content_url('cache/speed-optimizer/js/' . $file_hash . '.js');
        
        if (!file_exists($cache_file)) {
            $content = file_get_contents($file_path);
            $minified_content = $this->minify_js_content($content);
            file_put_contents($cache_file, $minified_content);
        }
        
        return $cache_url;
    }
    
    /**
     * Get local file path from URL
     */
    private function get_local_file_path($src) {
        $home_url = home_url();
        if (strpos($src, $home_url) === 0) {
            return ABSPATH . str_replace($home_url, '', $src);
        } elseif (strpos($src, '/') === 0) {
            return ABSPATH . ltrim($src, '/');
        }
        return false;
    }
    
    /**
     * Minify CSS content
     */
    private function minify_css_content($content) {
        // Remove comments
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        
        // Remove unnecessary whitespace
        $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $content);
        $content = str_replace(array('; ', ' ;', ' {', '{ ', ' }', '} ', ' :', ': ', ' ,', ', '), array(';', ';', '{', '{', '}', '}', ':', ':', ',', ','), $content);
        
        return trim($content);
    }
    
    /**
     * Minify JS content (basic minification)
     */
    private function minify_js_content($content) {
        // Remove single-line comments
        $content = preg_replace('/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/', '', $content);
        
        // Remove unnecessary whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace(array(' {', '{ ', ' }', '} ', ' (', '( ', ' )', ') ', ' ;', '; '), array('{', '{', '}', '}', '(', '(', ')', ')', ';', ';'), $content);
        
        return trim($content);
    }
    
    /**
     * Enable GZIP compression
     */
    public function enable_gzip_compression() {
        if (!is_admin() && !headers_sent()) {
            if (function_exists('gzencode') && !ob_get_level()) {
                ob_start(array($this, 'gzip_output'));
            }
        }
    }
    
    /**
     * GZIP output callback
     */
    public function gzip_output($buffer) {
        if (strlen($buffer) < 2048) {
            return $buffer;
        }
        
        $encoding = '';
        if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
            $encoding = $_SERVER['HTTP_ACCEPT_ENCODING'];
        }
        
        if (strpos($encoding, 'gzip') !== false) {
            header('Content-Encoding: gzip');
            return gzencode($buffer);
        }
        
        return $buffer;
    }
    
    /**
     * Setup caching headers
     */
    public function setup_caching() {
        if (is_admin()) {
            return;
        }
        
        $expiration = $this->options['cache_expiration'];
        header('Cache-Control: public, max-age=' . $expiration);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $expiration) . ' GMT');
    }
    
    /**
     * Add lazy loading script
     */
    /**
     * Add async/defer attributes to scripts
     */
    public function add_async_defer_attributes($tag, $handle) {
        $async_scripts = array('google-analytics', 'gtag');
        $defer_scripts = array('jquery', 'wp-embed');
        
        if (in_array($handle, $async_scripts)) {
            return str_replace(' src', ' async src', $tag);
        }
        
        if (in_array($handle, $defer_scripts)) {
            return str_replace(' src', ' defer src', $tag);
        }
        
        return $tag;
    }
    
    /**
     * Optimize image src
     */
    public function optimize_image_src($image, $attachment_id, $size, $icon) {
        if ($this->options['enable_image_optimization'] && is_array($image)) {
            // Add lazy loading data attribute
            $image['lazy'] = true;
        }
        return $image;
    }
    
    /**
     * Replace URLs with CDN
     */
    public function replace_with_cdn($url) {
        if (!empty($this->options['cdn_url']) && !is_admin()) {
            $home_url = home_url();
            if (strpos($url, $home_url) === 0) {
                return str_replace($home_url, rtrim($this->options['cdn_url'], '/'), $url);
            }
        }
        return $url;
    }
    
    /**
     * Database optimization
     */
    public function optimize_database() {
        // Check if feature is available based on license
        if (!$this->license->is_feature_available('simple_database_cleanup')) {
            return array('error' => 'Database optimization requires a premium license');
        }
        
        global $wpdb;
        
        $optimized = array();
        
        // Clean expired transients
        $transients_deleted = $wpdb->query("
            DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_timeout_%' 
            AND option_value < UNIX_TIMESTAMP()
        ");
        $optimized['transients'] = $transients_deleted;
        
        // Clean orphaned postmeta
        $postmeta_deleted = $wpdb->query("
            DELETE pm FROM {$wpdb->postmeta} pm
            LEFT JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.ID IS NULL
        ");
        $optimized['postmeta'] = $postmeta_deleted;
        
        // Clean spam comments (only for premium users)
        if ($this->license->is_feature_available('advanced_caching')) {
            $spam_deleted = $wpdb->query("
                DELETE FROM {$wpdb->comments} 
                WHERE comment_approved = 'spam'
            ");
            $optimized['spam_comments'] = $spam_deleted;
        
            // Optimize database tables
            $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            $optimized_tables = 0;
            foreach ($tables as $table) {
                $wpdb->query("OPTIMIZE TABLE {$table[0]}");
                $optimized_tables++;
            }
            $optimized['tables'] = $optimized_tables;
        }
        
        // Log the optimization
        $database = new Speed_Optimizer_Database();
        $database->log_action(
            'database_optimization',
            sprintf('Cleaned %d transients, %d orphaned postmeta',
                $transients_deleted, $postmeta_deleted
            )
        );
        
        return $optimized;
    }
    
    /**
     * Setup page caching system
     */
    public function setup_page_caching() {
        if (is_admin() || is_user_logged_in() && !$this->options['cache_logged_users']) {
            return;
        }
        
        // Skip cache for certain pages
        if ($this->should_skip_cache()) {
            return;
        }
        
        $cache_key = $this->get_cache_key();
        $cache_file = $this->get_cache_file_path($cache_key);
        
        // Serve cached version if exists and not expired
        if (file_exists($cache_file) && !$this->is_cache_expired($cache_file)) {
            header('X-Cache: HIT');
            $this->serve_cached_page($cache_file);
            exit;
        }
        
        // Start output buffering to capture page content
        ob_start(array($this, 'cache_page_output'));
    }
    
    /**
     * Check if current page should skip cache
     */
    private function should_skip_cache() {
        // Skip if POST request
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return true;
        }
        
        // Skip if query strings (unless enabled)
        if (!$this->options['cache_query_strings'] && !empty($_GET)) {
            return true;
        }
        
        // Skip WooCommerce cart and checkout pages
        if ($this->options['exclude_cart_checkout'] && function_exists('is_cart') && (is_cart() || is_checkout() || is_account_page())) {
            return true;
        }
        
        // Skip if cookies indicate user state
        if (isset($_COOKIE['wordpress_logged_in_' . COOKIEHASH]) || isset($_COOKIE['woocommerce_cart_hash'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Generate cache key for current request
     */
    private function get_cache_key() {
        $key = $_SERVER['REQUEST_URI'];
        
        // Add mobile cache distinction
        if ($this->options['enable_mobile_cache'] && wp_is_mobile()) {
            $key .= '_mobile';
        }
        
        // Add user agent for device-specific caching
        if ($this->options['user_agent_cache']) {
            $key .= '_' . md5($_SERVER['HTTP_USER_AGENT']);
        }
        
        return md5($key);
    }
    
    /**
     * Get cache file path
     */
    private function get_cache_file_path($cache_key) {
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/pages/';
        wp_mkdir_p($cache_dir);
        return $cache_dir . $cache_key . '.html';
    }
    
    /**
     * Check if cache file is expired
     */
    private function is_cache_expired($cache_file) {
        $file_time = filemtime($cache_file);
        $expiration = $this->options['cache_expiration'];
        return (time() - $file_time) > $expiration;
    }
    
    /**
     * Serve cached page
     */
    private function serve_cached_page($cache_file) {
        $content = file_get_contents($cache_file);
        $content .= "\n<!-- Cached by Speed Optimizer at " . date('Y-m-d H:i:s', filemtime($cache_file)) . " -->";
        echo $content;
    }
    
    /**
     * Cache page output
     */
    public function cache_page_output($buffer) {
        // Only cache HTML content
        if (strlen($buffer) < 255 || !$this->is_html_content($buffer)) {
            return $buffer;
        }
        
        $cache_key = $this->get_cache_key();
        $cache_file = $this->get_cache_file_path($cache_key);
        
        // Save to cache
        file_put_contents($cache_file, $buffer);
        
        // Add cache header
        header('X-Cache: MISS');
        
        return $buffer;
    }
    
    /**
     * Check if content is HTML
     */
    private function is_html_content($buffer) {
        return strpos($buffer, '<html') !== false || strpos($buffer, '<!DOCTYPE') !== false;
    }
    
    /**
     * Preload cache when content is updated
     */
    public function preload_cache_on_update($post_id = null) {
        if (!$this->license->is_feature_available('cache_preloading')) {
            return;
        }
        
        // Schedule cache preloading
        wp_schedule_single_event(time() + 5, 'speed_optimizer_preload_cache', array($post_id));
        
        if (!wp_next_scheduled('speed_optimizer_preload_cache')) {
            add_action('speed_optimizer_preload_cache', array($this, 'preload_important_pages'));
        }
    }
    
    /**
     * Preload important pages
     */
    public function preload_important_pages($post_id = null) {
        $urls_to_preload = array(
            home_url(),
            get_permalink(get_option('page_for_posts')),
            get_permalink(get_option('page_on_front'))
        );
        
        // Add updated post URL
        if ($post_id) {
            $urls_to_preload[] = get_permalink($post_id);
        }
        
        foreach ($urls_to_preload as $url) {
            if ($url) {
                wp_remote_get($url, array('timeout' => 10, 'blocking' => false));
            }
        }
    }
    
    /**
     * File concatenation
     */
    public function concatenate_files() {
        if (is_admin()) {
            return;
        }
        
        global $wp_scripts, $wp_styles;
        
        // Concatenate CSS files
        if ($wp_styles && $this->should_concatenate_css()) {
            $this->concatenate_css_files($wp_styles);
        }
        
        // Concatenate JS files
        if ($wp_scripts && $this->should_concatenate_js()) {
            $this->concatenate_js_files($wp_scripts);
        }
    }
    
    /**
     * Check if CSS concatenation should be applied
     */
    private function should_concatenate_css() {
        return $this->options['optimization_level'] === 'aggressive';
    }
    
    /**
     * Check if JS concatenation should be applied
     */
    private function should_concatenate_js() {
        return $this->options['optimization_level'] === 'aggressive';
    }
    
    /**
     * Concatenate CSS files
     */
    private function concatenate_css_files($wp_styles) {
        $css_files = array();
        $excluded_handles = array('admin-bar', 'dashicons');
        
        foreach ($wp_styles->queue as $handle) {
            if (!in_array($handle, $excluded_handles)) {
                $style = $wp_styles->registered[$handle];
                if ($style && $this->is_local_file($style->src)) {
                    $css_files[] = $handle;
                }
            }
        }
        
        if (count($css_files) > 1) {
            $concatenated_url = $this->create_concatenated_css_file($css_files, $wp_styles);
            if ($concatenated_url) {
                // Remove individual files and add concatenated version
                foreach ($css_files as $handle) {
                    wp_dequeue_style($handle);
                }
                wp_enqueue_style('speed-optimizer-concatenated', $concatenated_url);
            }
        }
    }
    
    /**
     * Create concatenated CSS file
     */
    private function create_concatenated_css_file($handles, $wp_styles) {
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/concat/';
        wp_mkdir_p($cache_dir);
        
        $content_hash = '';
        $combined_content = '';
        
        foreach ($handles as $handle) {
            $style = $wp_styles->registered[$handle];
            $file_path = $this->get_local_file_path($style->src);
            if ($file_path && file_exists($file_path)) {
                $content = file_get_contents($file_path);
                $combined_content .= "/* $handle */\n" . $content . "\n";
                $content_hash .= filemtime($file_path);
            }
        }
        
        $file_hash = md5($content_hash);
        $cache_file = $cache_dir . 'styles-' . $file_hash . '.css';
        $cache_url = content_url('cache/speed-optimizer/concat/styles-' . $file_hash . '.css');
        
        if (!file_exists($cache_file)) {
            $minified_content = $this->minify_css_content($combined_content);
            file_put_contents($cache_file, $minified_content);
        }
        
        return $cache_url;
    }
    
    /**
     * Concatenate JS files
     */
    private function concatenate_js_files($wp_scripts) {
        $js_files = array();
        $excluded_handles = array('jquery', 'wp-embed', 'admin-bar');
        
        foreach ($wp_scripts->queue as $handle) {
            if (!in_array($handle, $excluded_handles)) {
                $script = $wp_scripts->registered[$handle];
                if ($script && $this->is_local_file($script->src)) {
                    $js_files[] = $handle;
                }
            }
        }
        
        if (count($js_files) > 1) {
            $concatenated_url = $this->create_concatenated_js_file($js_files, $wp_scripts);
            if ($concatenated_url) {
                // Remove individual files and add concatenated version
                foreach ($js_files as $handle) {
                    wp_dequeue_script($handle);
                }
                wp_enqueue_script('speed-optimizer-concatenated', $concatenated_url);
            }
        }
    }
    
    /**
     * Create concatenated JS file
     */
    private function create_concatenated_js_file($handles, $wp_scripts) {
        $cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/concat/';
        wp_mkdir_p($cache_dir);
        
        $content_hash = '';
        $combined_content = '';
        
        foreach ($handles as $handle) {
            $script = $wp_scripts->registered[$handle];
            $file_path = $this->get_local_file_path($script->src);
            if ($file_path && file_exists($file_path)) {
                $content = file_get_contents($file_path);
                $combined_content .= "/* $handle */\n" . $content . ";\n";
                $content_hash .= filemtime($file_path);
            }
        }
        
        $file_hash = md5($content_hash);
        $cache_file = $cache_dir . 'scripts-' . $file_hash . '.js';
        $cache_url = content_url('cache/speed-optimizer/concat/scripts-' . $file_hash . '.js');
        
        if (!file_exists($cache_file)) {
            $minified_content = $this->minify_js_content($combined_content);
            file_put_contents($cache_file, $minified_content);
        }
        
        return $cache_url;
    }
    
    /**
     * Add lazy loading to content images
     */
    public function add_lazy_loading_to_content($content) {
        if (!$this->options['enable_image_optimization'] || is_admin() || is_feed()) {
            return $content;
        }
        
        // Add lazy loading and dimensions to images
        $content = preg_replace_callback(
            '/<img([^>]+?)src=[\'"]?([^\'"\s>]+)[\'"]?([^>]*)>/i',
            array($this, 'process_image_for_lazy_loading'),
            $content
        );
        
        return $content;
    }
    
    /**
     * Process image for lazy loading
     */
    private function process_image_for_lazy_loading($matches) {
        $img_tag = $matches[0];
        $before_src = $matches[1];
        $src = $matches[2];
        $after_src = $matches[3];
        
        // Skip if already has data-src or is SVG
        if (strpos($img_tag, 'data-src') !== false || strpos($src, '.svg') !== false) {
            return $img_tag;
        }
        
        // Add lazy loading attributes
        $lazy_img = '<img' . $before_src . 'src="data:image/svg+xml,%3Csvg%20xmlns=\'http://www.w3.org/2000/svg\'%20viewBox=\'0%200%201%201\'%3E%3C/svg%3E" data-src="' . $src . '"' . $after_src;
        
        // Add dimensions if enabled and missing
        if ($this->options['add_image_dimensions'] && !preg_match('/\s(width|height)=/', $lazy_img)) {
            $lazy_img = $this->add_image_dimensions($lazy_img, $src);
        }
        
        // Add lazy class
        if (strpos($lazy_img, 'class=') !== false) {
            $lazy_img = preg_replace('/class=[\'"]([^\'"]*)[\'"]/', 'class="$1 lazy"', $lazy_img);
        } else {
            $lazy_img = str_replace('<img', '<img class="lazy"', $lazy_img);
        }
        
        return $lazy_img . '>';
    }
    
    /**
     * Add image dimensions
     */
    private function add_image_dimensions($img_tag, $src) {
        // Get image dimensions from file
        $file_path = $this->get_local_file_path($src);
        if ($file_path && file_exists($file_path)) {
            $image_size = getimagesize($file_path);
            if ($image_size) {
                $img_tag = str_replace('<img', '<img width="' . $image_size[0] . '" height="' . $image_size[1] . '"', $img_tag);
            }
        }
        
        return $img_tag;
    }
    
    /**
     * Convert images to WebP format
     */
    public function convert_to_webp($image, $attachment_id, $size, $icon) {
        if (!$image || empty($image[0]) || !$this->webp_supported()) {
            return $image;
        }
        
        $webp_url = $this->generate_webp_version($image[0]);
        if ($webp_url) {
            $image[0] = $webp_url;
        }
        
        return $image;
    }
    
    /**
     * Check if WebP is supported
     */
    private function webp_supported() {
        return isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'image/webp') !== false;
    }
    
    /**
     * Generate WebP version of image
     */
    private function generate_webp_version($image_url) {
        $file_path = $this->get_local_file_path($image_url);
        if (!$file_path || !file_exists($file_path)) {
            return false;
        }
        
        $pathinfo = pathinfo($file_path);
        $webp_file = $pathinfo['dirname'] . '/' . $pathinfo['filename'] . '.webp';
        $webp_url = str_replace($pathinfo['basename'], $pathinfo['filename'] . '.webp', $image_url);
        
        // Create WebP version if it doesn't exist
        if (!file_exists($webp_file)) {
            if (!$this->convert_image_to_webp($file_path, $webp_file)) {
                return false;
            }
        }
        
        return $webp_url;
    }
    
    /**
     * Convert image to WebP
     */
    private function convert_image_to_webp($source, $destination) {
        if (!function_exists('imagewebp')) {
            return false;
        }
        
        $image_info = getimagesize($source);
        if (!$image_info) {
            return false;
        }
        
        $mime_type = $image_info['mime'];
        
        switch ($mime_type) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            default:
                return false;
        }
        
        if (!$image) {
            return false;
        }
        
        $result = imagewebp($image, $destination, 85);
        imagedestroy($image);
        
        return $result;
    }
    
    /**
     * Disable WordPress emoji scripts
     */
    public function disable_emoji_scripts() {
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        
        // Remove from TinyMCE
        add_filter('tiny_mce_plugins', array($this, 'disable_emojis_tinymce'));
        add_filter('wp_resource_hints', array($this, 'disable_emojis_remove_dns_prefetch'), 10, 2);
    }
    
    /**
     * Remove emoji from TinyMCE
     */
    public function disable_emojis_tinymce($plugins) {
        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        }
        return array();
    }
    
    /**
     * Remove emoji DNS prefetch
     */
    public function disable_emojis_remove_dns_prefetch($urls, $relation_type) {
        if ('dns-prefetch' == $relation_type) {
            $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');
            $urls = array_diff($urls, array($emoji_svg_url));
        }
        return $urls;
    }
    
    /**
     * Setup WooCommerce optimizations
     */
    public function setup_woocommerce_optimizations() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Skip cart fragments on non-shop pages
        if ($this->options['refresh_cart_fragments']) {
            add_action('wp_enqueue_scripts', array($this, 'optimize_woocommerce_scripts'), 99);
        }
        
        // Disable cart fragmentation on specific pages
        if ($this->options['exclude_cart_checkout']) {
            add_action('wp_enqueue_scripts', array($this, 'disable_cart_fragments_on_non_shop_pages'), 99);
        }
    }
    
    /**
     * Optimize WooCommerce scripts
     */
    public function optimize_woocommerce_scripts() {
        if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product() && !is_cart() && !is_checkout() && !is_account_page()) {
            wp_dequeue_script('wc-cart-fragments');
        }
    }
    
    /**
     * Disable cart fragments on non-shop pages
     */
    public function disable_cart_fragments_on_non_shop_pages() {
        if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page()) {
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_script('woocommerce');
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_style('woocommerce-smallscreen');
        }
    }
    
    /**
     * Control WordPress heartbeat
     */
    public function control_heartbeat() {
        if (!$this->license->is_feature_available('heartbeat_control')) {
            return;
        }
        
        // Disable heartbeat on frontend
        add_action('wp_enqueue_scripts', array($this, 'disable_heartbeat_frontend'));
        
        // Modify heartbeat frequency in admin
        add_filter('heartbeat_settings', array($this, 'modify_heartbeat_settings'));
    }
    
    /**
     * Disable heartbeat on frontend
     */
    public function disable_heartbeat_frontend() {
        if (!is_admin()) {
            wp_deregister_script('heartbeat');
        }
    }
    
    /**
     * Modify heartbeat settings
     */
    public function modify_heartbeat_settings($settings) {
        $settings['interval'] = $this->options['heartbeat_frequency'];
        return $settings;
    }
    
    /**
     * Optimize CSS delivery
     */
    public function optimize_css_delivery($tag, $handle) {
        // Skip critical CSS files
        $critical_handles = array('critical-css', 'admin-bar');
        if (in_array($handle, $critical_handles)) {
            return $tag;
        }
        
        // Add preload for non-critical CSS
        if ($this->options['defer_js_loading'] && $this->license->is_feature_available('defer_css_loading')) {
            $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
            $tag .= '<noscript>' . str_replace('rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', 'rel="stylesheet"', $tag) . '</noscript>';
        }
        
        return $tag;
    }
    
    /**
     * Delay JavaScript execution
     */
    public function delay_javascript_execution($tag, $handle) {
        if (!$this->license->is_feature_available('delay_js_execution')) {
            return $tag;
        }
        
        // Don't delay critical scripts
        $critical_scripts = array('jquery', 'jquery-core', 'jquery-migrate');
        if (in_array($handle, $critical_scripts)) {
            return $tag;
        }
        
        // Add delay attributes
        return str_replace(' src', ' data-src', str_replace('<script', '<script type="text/delayed"', $tag));
    }
    
    /**
     * Defer JavaScript loading
     */
    public function defer_javascript_loading($tag, $handle) {
        if (!$this->license->is_feature_available('defer_js_loading')) {
            return $tag;
        }
        
        // Don't defer critical scripts
        $critical_scripts = array('jquery', 'jquery-core', 'jquery-migrate');
        if (in_array($handle, $critical_scripts)) {
            return $tag;
        }
        
        // Add defer attribute
        return str_replace(' src', ' defer src', $tag);
    }
    
    /**
     * Enhanced performance headers with preloading
     */
    public function add_performance_headers() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        
        // Preconnect to external domains
        $preconnect_domains = array_filter(explode("\n", $this->options['preconnect_domains']));
        $default_domains = array('fonts.googleapis.com', 'fonts.gstatic.com');
        $all_domains = array_merge($default_domains, $preconnect_domains);
        
        foreach ($all_domains as $domain) {
            $domain = trim($domain);
            if (!empty($domain)) {
                echo '<link rel="preconnect" href="https://' . esc_attr($domain) . '">' . "\n";
            }
        }
        
        // Preload fonts if enabled
        if ($this->options['preload_fonts'] && $this->license->is_feature_available('preload_fonts')) {
            $this->add_font_preloading();
        }
        
        // DNS prefetch for common services
        echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
        echo '<link rel="dns-prefetch" href="//www.googletagmanager.com">' . "\n";
    }
    
    /**
     * Add font preloading
     */
    private function add_font_preloading() {
        // Get theme directory for font discovery
        $theme_dir = get_template_directory();
        $font_extensions = array('woff2', 'woff', 'ttf');
        
        foreach ($font_extensions as $ext) {
            $font_files = glob($theme_dir . "/assets/fonts/*.$ext");
            foreach ($font_files as $font_file) {
                $font_url = get_template_directory_uri() . '/assets/fonts/' . basename($font_file);
                echo '<link rel="preload" href="' . esc_url($font_url) . '" as="font" type="font/' . $ext . '" crossorigin>' . "\n";
                break; // Only preload one format per font
            }
        }
    }
    
    /**
     * Enhanced lazy loading script with link preloading
     */
    public function add_lazy_loading_script() {
        if ($this->options['enable_image_optimization']) {
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                // Lazy loading for images
                var lazyImages = document.querySelectorAll("img[data-src]");
                if ("IntersectionObserver" in window) {
                    var imageObserver = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                var image = entry.target;
                                image.src = image.dataset.src;
                                image.classList.remove("lazy");
                                imageObserver.unobserve(image);
                            }
                        });
                    });
                    lazyImages.forEach(function(image) {
                        imageObserver.observe(image);
                    });
                }
                
                // Link preloading on hover
                if (' . ($this->options['preload_links'] ? 'true' : 'false') . ') {
                    var links = document.querySelectorAll("a[href^=\"/\"], a[href^=\"' . home_url() . '\"]");
                    var prefetched = [];
                    
                    function prefetchPage(url) {
                        if (prefetched.indexOf(url) === -1) {
                            var link = document.createElement("link");
                            link.rel = "prefetch";
                            link.href = url;
                            document.head.appendChild(link);
                            prefetched.push(url);
                        }
                    }
                    
                    links.forEach(function(link) {
                        link.addEventListener("mouseenter", function() {
                            prefetchPage(this.href);
                        });
                    });
                }
                
                // Delayed JavaScript execution
                if (document.querySelectorAll("script[type=\"text/delayed\"]").length > 0) {
                    var delayedScripts = document.querySelectorAll("script[type=\"text/delayed\"]");
                    var loadDelayedScripts = function() {
                        delayedScripts.forEach(function(script) {
                            script.type = "text/javascript";
                            if (script.dataset.src) {
                                script.src = script.dataset.src;
                            }
                        });
                    };
                    
                    // Load delayed scripts on user interaction
                    ["mousedown", "mousemove", "keypress", "scroll", "touchstart"].forEach(function(event) {
                        document.addEventListener(event, loadDelayedScripts, {once: true});
                    });
                    
                    // Fallback: load after 5 seconds
                    setTimeout(loadDelayedScripts, 5000);
                }
            });
            </script>';
        }
    }
    
    /**
     * Clear all caches
     */
    public function clear_all_caches() {
        // Clear page cache
        $page_cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/pages/';
        $this->clear_directory($page_cache_dir);
        
        // Clear minified files cache
        $css_cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/css/';
        $js_cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/js/';
        $concat_cache_dir = WP_CONTENT_DIR . '/cache/speed-optimizer/concat/';
        
        $this->clear_directory($css_cache_dir);
        $this->clear_directory($js_cache_dir);
        $this->clear_directory($concat_cache_dir);
        
        // Clear WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        return true;
    }
    
    /**
     * Clear directory contents
     */
    private function clear_directory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->clear_directory($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}