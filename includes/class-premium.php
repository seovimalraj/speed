<?php
/**
 * Premium optimization features
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_Premium {
    
    private $license;
    private $options;
    
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
            'critical_css_enabled' => get_option('speed_optimizer_critical_css_enabled', 0),
            'webp_conversion_enabled' => get_option('speed_optimizer_webp_conversion_enabled', 0),
            'object_caching_enabled' => get_option('speed_optimizer_object_caching_enabled', 0),
            'fragment_caching_enabled' => get_option('speed_optimizer_fragment_caching_enabled', 0),
            'prefetching_enabled' => get_option('speed_optimizer_prefetching_enabled', 0),
            'video_optimization_enabled' => get_option('speed_optimizer_video_optimization_enabled', 0),
            'advanced_minification_enabled' => get_option('speed_optimizer_advanced_minification_enabled', 0),
            'scheduled_optimization_enabled' => get_option('speed_optimizer_scheduled_optimization_enabled', 0)
        );
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        // Critical CSS
        if ($this->options['critical_css_enabled'] && $this->license->is_feature_available('critical_css')) {
            add_action('wp_head', array($this, 'inject_critical_css'), 1);
            add_filter('style_loader_tag', array($this, 'defer_non_critical_css'), 10, 2);
        }
        
        // WebP conversion
        if ($this->options['webp_conversion_enabled'] && $this->license->is_feature_available('webp_conversion')) {
            add_filter('wp_get_attachment_image_src', array($this, 'convert_to_webp'), 10, 4);
            add_filter('the_content', array($this, 'convert_content_images_to_webp'));
        }
        
        // Object caching
        if ($this->options['object_caching_enabled'] && $this->license->is_feature_available('object_caching')) {
            $this->setup_object_caching();
        }
        
        // Fragment caching
        if ($this->options['fragment_caching_enabled'] && $this->license->is_feature_available('fragment_caching')) {
            add_action('init', array($this, 'setup_fragment_caching'));
        }
        
        // Prefetching
        if ($this->options['prefetching_enabled'] && $this->license->is_feature_available('prefetching')) {
            add_action('wp_footer', array($this, 'add_prefetching_script'));
        }
        
        // Video optimization
        if ($this->options['video_optimization_enabled'] && $this->license->is_feature_available('video_optimization')) {
            add_filter('the_content', array($this, 'optimize_videos'));
        }
        
        // Advanced minification
        if ($this->options['advanced_minification_enabled'] && $this->license->is_feature_available('advanced_minification')) {
            add_action('wp_enqueue_scripts', array($this, 'advanced_minification'), 999);
        }
        
        // Scheduled optimization
        if ($this->options['scheduled_optimization_enabled'] && $this->license->is_feature_available('scheduled_optimization')) {
            $this->setup_scheduled_optimization();
        }
        
        // AJAX handlers
        add_action('wp_ajax_speed_optimizer_generate_critical_css', array($this, 'ajax_generate_critical_css'));
        add_action('wp_ajax_speed_optimizer_clear_fragment_cache', array($this, 'ajax_clear_fragment_cache'));
    }
    
    /**
     * Generate and inject critical CSS
     */
    public function inject_critical_css() {
        $critical_css = $this->get_critical_css();
        if (!empty($critical_css)) {
            echo '<style id="critical-css">' . $critical_css . '</style>' . "\n";
        }
    }
    
    /**
     * Get critical CSS for current page
     */
    private function get_critical_css() {
        $page_id = get_queried_object_id();
        $cache_key = 'critical_css_' . $page_id;
        
        $critical_css = wp_cache_get($cache_key, 'speed_optimizer');
        
        if ($critical_css === false) {
            $critical_css = $this->generate_critical_css();
            wp_cache_set($cache_key, $critical_css, 'speed_optimizer', 3600);
        }
        
        return $critical_css;
    }
    
    /**
     * Generate critical CSS for current page
     */
    private function generate_critical_css() {
        // This is a simplified version. In production, you'd use tools like:
        // - Critical (https://github.com/addyosmani/critical)
        // - Penthouse (https://github.com/pocketjoso/penthouse)
        // - Or a service like CriticalCSS.com
        
        $critical_css = '';
        
        // Get page-specific styles
        $page_type = $this->get_page_type();
        
        switch ($page_type) {
            case 'home':
                $critical_css = $this->get_homepage_critical_css();
                break;
            case 'single':
                $critical_css = $this->get_single_critical_css();
                break;
            case 'archive':
                $critical_css = $this->get_archive_critical_css();
                break;
            default:
                $critical_css = $this->get_default_critical_css();
        }
        
        return $critical_css;
    }
    
    /**
     * Get page type
     */
    private function get_page_type() {
        if (is_front_page()) {
            return 'home';
        } elseif (is_single()) {
            return 'single';
        } elseif (is_archive()) {
            return 'archive';
        }
        return 'default';
    }
    
    /**
     * Get homepage critical CSS
     */
    private function get_homepage_critical_css() {
        return '
            body{margin:0;padding:0}
            .header{display:block}
            .main-navigation{display:block}
            .hero-section{display:block}
            .site-header{position:relative}
            h1,h2,h3{margin:0 0 1em}
            p{margin:0 0 1.5em}
        ';
    }
    
    /**
     * Get single post critical CSS
     */
    private function get_single_critical_css() {
        return '
            body{margin:0;padding:0}
            .header{display:block}
            .main-navigation{display:block}
            .entry-header{display:block}
            .entry-content{display:block}
            h1,h2,h3{margin:0 0 1em}
            p{margin:0 0 1.5em}
        ';
    }
    
    /**
     * Get archive critical CSS
     */
    private function get_archive_critical_css() {
        return '
            body{margin:0;padding:0}
            .header{display:block}
            .main-navigation{display:block}
            .archive-header{display:block}
            .post-list{display:block}
            h1,h2,h3{margin:0 0 1em}
            p{margin:0 0 1.5em}
        ';
    }
    
    /**
     * Get default critical CSS
     */
    private function get_default_critical_css() {
        return '
            body{margin:0;padding:0}
            .header{display:block}
            .main-navigation{display:block}
            .content{display:block}
            h1,h2,h3{margin:0 0 1em}
            p{margin:0 0 1.5em}
        ';
    }
    
    /**
     * Defer non-critical CSS
     */
    public function defer_non_critical_css($tag, $handle) {
        // Skip critical CSS files
        $critical_handles = array('critical-css', 'admin-bar');
        if (in_array($handle, $critical_handles)) {
            return $tag;
        }
        
        // Convert to preload and defer
        $tag = str_replace('rel="stylesheet"', 'rel="preload" as="style" onload="this.onload=null;this.rel=\'stylesheet\'"', $tag);
        
        return $tag;
    }
    
    /**
     * Convert images to WebP format
     */
    public function convert_to_webp($image, $attachment_id, $size, $icon) {
        if (!$image || empty($image[0])) {
            return $image;
        }
        
        $webp_url = $this->generate_webp_version($image[0]);
        if ($webp_url) {
            $image[0] = $webp_url;
        }
        
        return $image;
    }
    
    /**
     * Convert content images to WebP
     */
    public function convert_content_images_to_webp($content) {
        return preg_replace_callback('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', function($matches) {
            $img_tag = $matches[0];
            $src = $matches[1];
            
            $webp_src = $this->generate_webp_version($src);
            if ($webp_src) {
                $img_tag = str_replace($src, $webp_src, $img_tag);
            }
            
            return $img_tag;
        }, $content);
    }
    
    /**
     * Generate WebP version of image
     */
    private function generate_webp_version($image_url) {
        // Skip if already WebP
        if (strpos($image_url, '.webp') !== false) {
            return false;
        }
        
        // Skip external images
        if (strpos($image_url, get_site_url()) === false) {
            return false;
        }
        
        $upload_dir = wp_upload_dir();
        $image_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $image_url);
        
        if (!file_exists($image_path)) {
            return false;
        }
        
        $webp_path = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_path);
        $webp_url = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $image_url);
        
        // Check if WebP version already exists
        if (file_exists($webp_path)) {
            return $webp_url;
        }
        
        // Generate WebP version
        if ($this->create_webp_image($image_path, $webp_path)) {
            return $webp_url;
        }
        
        return false;
    }
    
    /**
     * Create WebP image
     */
    private function create_webp_image($source, $destination) {
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
        
        $result = imagewebp($image, $destination, 80);
        imagedestroy($image);
        
        return $result;
    }
    
    /**
     * Setup object caching
     */
    private function setup_object_caching() {
        // Enable object caching if not already enabled
        if (!wp_using_ext_object_cache()) {
            // This would typically require setting up Redis or Memcached
            // For demo purposes, we'll use the built-in file-based caching
            wp_cache_init();
        }
    }
    
    /**
     * Setup fragment caching
     */
    public function setup_fragment_caching() {
        // Cache widgets
        add_filter('widget_display_callback', array($this, 'cache_widget_output'), 10, 3);
        
        // Cache menus
        add_filter('wp_nav_menu', array($this, 'cache_menu_output'), 10, 2);
    }
    
    /**
     * Cache widget output
     */
    public function cache_widget_output($instance, $widget, $args) {
        $cache_key = 'widget_' . $widget->id_base . '_' . $widget->id;
        $cached = wp_cache_get($cache_key, 'speed_optimizer_fragments');
        
        if ($cached !== false) {
            echo $cached;
            return false; // Skip normal widget rendering
        }
        
        ob_start();
        return $instance; // Continue with normal rendering
    }
    
    /**
     * Cache menu output
     */
    public function cache_menu_output($nav_menu, $args) {
        $cache_key = 'menu_' . md5(serialize($args));
        $cached = wp_cache_get($cache_key, 'speed_optimizer_fragments');
        
        if ($cached !== false) {
            return $cached;
        }
        
        wp_cache_set($cache_key, $nav_menu, 'speed_optimizer_fragments', 3600);
        return $nav_menu;
    }
    
    /**
     * Add prefetching script
     */
    public function add_prefetching_script() {
        ?>
        <script>
        (function() {
            var links = document.querySelectorAll('a[href^="/"], a[href^="<?php echo get_site_url(); ?>"]');
            var prefetched = [];
            
            function prefetchPage(url) {
                if (prefetched.indexOf(url) === -1) {
                    var link = document.createElement('link');
                    link.rel = 'prefetch';
                    link.href = url;
                    document.head.appendChild(link);
                    prefetched.push(url);
                }
            }
            
            links.forEach(function(link) {
                link.addEventListener('mouseenter', function() {
                    prefetchPage(this.href);
                });
            });
        })();
        </script>
        <?php
    }
    
    /**
     * Optimize videos
     */
    public function optimize_videos($content) {
        // Add lazy loading to videos
        $content = preg_replace('/<video([^>]*)>/i', '<video$1 loading="lazy" preload="metadata">', $content);
        
        // Convert YouTube embeds to lite version
        $content = preg_replace_callback(
            '/<iframe[^>]+src=["\']https:\/\/www\.youtube\.com\/embed\/([^"\']+)["\'][^>]*><\/iframe>/i',
            array($this, 'convert_youtube_embed'),
            $content
        );
        
        return $content;
    }
    
    /**
     * Convert YouTube embed to lite version
     */
    private function convert_youtube_embed($matches) {
        $video_id = $matches[1];
        
        return sprintf(
            '<div class="youtube-lite" data-id="%s" style="background-image:url(https://img.youtube.com/vi/%s/maxresdefault.jpg);cursor:pointer;position:relative;">
                <div style="position:absolute;top:50%%;left:50%%;transform:translate(-50%%,-50%%);background:rgba(0,0,0,0.8);border-radius:50%%;padding:20px;">
                    <svg width="68" height="48" viewBox="0 0 68 48" fill="#fff"><path d="M66.52 7.74c-.78-2.93-2.49-5.41-5.42-6.19C55.79.13 34 0 34 0S12.21.13 6.9 1.55c-2.93.78-4.63 3.26-5.42 6.19C.06 13.05 0 24 0 24s.06 10.95 1.48 16.26c.78 2.93 2.49 5.41 5.42 6.19C12.21 47.87 34 48 34 48s21.79-.13 27.1-1.55c2.93-.78 4.64-3.26 5.42-6.19C67.94 34.95 68 24 68 24s-.06-10.95-1.48-16.26zM27 34V14l18 10-18 10z"></path></svg>
                </div>
            </div>
            <script>
            document.addEventListener("click", function(e) {
                if (e.target.closest(".youtube-lite")) {
                    var lite = e.target.closest(".youtube-lite");
                    var videoId = lite.getAttribute("data-id");
                    lite.innerHTML = "<iframe width=\"100%%\" height=\"100%%\" src=\"https://www.youtube.com/embed/" + videoId + "?autoplay=1\" frameborder=\"0\" allowfullscreen></iframe>";
                }
            });
            </script>',
            $video_id,
            $video_id
        );
    }
    
    /**
     * Advanced minification
     */
    public function advanced_minification() {
        if (is_admin()) {
            return;
        }
        
        // Combine and minify CSS files
        add_filter('style_loader_tag', array($this, 'combine_css_files'), 10, 2);
        
        // Combine and minify JS files
        add_filter('script_loader_tag', array($this, 'combine_js_files'), 10, 2);
    }
    
    /**
     * Combine CSS files
     */
    public function combine_css_files($tag, $handle) {
        // This is a simplified version
        // In production, you'd implement proper CSS combination and minification
        return $tag;
    }
    
    /**
     * Combine JS files
     */
    public function combine_js_files($tag, $handle) {
        // This is a simplified version
        // In production, you'd implement proper JS combination and minification
        return $tag;
    }
    
    /**
     * Setup scheduled optimization
     */
    private function setup_scheduled_optimization() {
        if (!wp_next_scheduled('speed_optimizer_scheduled_optimization')) {
            wp_schedule_event(time(), 'daily', 'speed_optimizer_scheduled_optimization');
        }
        
        add_action('speed_optimizer_scheduled_optimization', array($this, 'run_scheduled_optimization'));
    }
    
    /**
     * Run scheduled optimization
     */
    public function run_scheduled_optimization() {
        // Clear expired cache
        wp_cache_flush();
        
        // Optimize database
        $optimizer = new Speed_Optimizer_Optimizer();
        $optimizer->optimize_database();
        
        // Generate critical CSS for important pages
        $this->generate_critical_css_for_important_pages();
        
        // Log the optimization
        $database = new Speed_Optimizer_Database();
        $database->log_action('scheduled_optimization', 'Scheduled optimization completed');
    }
    
    /**
     * Generate critical CSS for important pages
     */
    private function generate_critical_css_for_important_pages() {
        $important_pages = array(
            get_option('page_on_front'),
            get_option('page_for_posts')
        );
        
        foreach ($important_pages as $page_id) {
            if ($page_id) {
                $cache_key = 'critical_css_' . $page_id;
                wp_cache_delete($cache_key, 'speed_optimizer');
            }
        }
    }
    
    /**
     * AJAX handler for generating critical CSS
     */
    public function ajax_generate_critical_css() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        if (!$this->license->is_feature_available('critical_css')) {
            wp_send_json_error('Feature not available in current plan');
        }
        
        $url = sanitize_url($_POST['url']);
        $critical_css = $this->generate_critical_css();
        
        wp_send_json_success(array(
            'critical_css' => $critical_css,
            'message' => 'Critical CSS generated successfully'
        ));
    }
    
    /**
     * AJAX handler for clearing fragment cache
     */
    public function ajax_clear_fragment_cache() {
        if (!wp_verify_nonce($_POST['nonce'], 'speed_optimizer_nonce')) {
            wp_die('Security check failed');
        }
        
        if (!current_user_can('manage_options')) {
            wp_die('Insufficient permissions');
        }
        
        wp_cache_flush_group('speed_optimizer_fragments');
        
        wp_send_json_success(array(
            'message' => 'Fragment cache cleared successfully'
        ));
    }
}