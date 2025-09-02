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
            'cache_expiration' => get_option('speed_optimizer_cache_expiration', 86400)
        );
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Basic features available to all tiers
        if ($this->options['enable_minification'] && $this->license->is_feature_available('basic_minification')) {
            add_action('wp_enqueue_scripts', array($this, 'minify_scripts'), 999);
        }
        
        if ($this->options['enable_compression'] && $this->license->is_feature_available('gzip_compression')) {
            add_action('init', array($this, 'enable_gzip_compression'));
        }
        
        if ($this->options['enable_caching'] && $this->license->is_feature_available('basic_caching')) {
            add_action('init', array($this, 'setup_caching'));
        }
        
        if ($this->options['enable_image_optimization'] && $this->license->is_feature_available('basic_lazy_loading')) {
            add_filter('wp_get_attachment_image_src', array($this, 'optimize_image_src'), 10, 4);
        }
        
        // Premium CDN feature
        if (!empty($this->options['cdn_url']) && $this->license->is_feature_available('cdn_integration')) {
            add_filter('wp_get_attachment_url', array($this, 'replace_with_cdn'));
        }
        
        // General optimization hooks
        add_action('wp_head', array($this, 'add_performance_headers'), 1);
        add_action('wp_footer', array($this, 'add_lazy_loading_script'), 999);
        add_filter('script_loader_tag', array($this, 'add_async_defer_attributes'), 10, 2);
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
     * Add performance headers
     */
    public function add_performance_headers() {
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
        echo '<link rel="dns-prefetch" href="//www.google-analytics.com">' . "\n";
    }
    
    /**
     * Add lazy loading script
     */
    public function add_lazy_loading_script() {
        if ($this->options['enable_image_optimization']) {
            echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
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
            });
            </script>';
        }
    }
    
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
}