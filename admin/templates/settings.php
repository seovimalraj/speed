<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin = new Speed_Optimizer_Admin();
$settings = $admin->get_settings();
$license = new Speed_Optimizer_License();
$current_tier = $license->get_license_tier();

// Handle form submission
if (isset($_POST['submit']) && wp_verify_nonce($_POST['_wpnonce'], 'speed_optimizer_settings')) {
    foreach ($settings as $key => $value) {
        if (isset($_POST[$key])) {
            $option_name = 'speed_optimizer_' . $key;
            $new_value = sanitize_text_field($_POST[$key]);
            update_option($option_name, $new_value);
        }
    }
    
    echo '<div class="notice notice-success"><p>' . __('Settings saved successfully!', 'speed-optimizer') . '</p></div>';
    
    // Reload settings
    $settings = $admin->get_settings();
}
?>

<div class="wrap speed-optimizer-settings">
    <h1><?php _e('Speed Optimizer Settings', 'speed-optimizer'); ?></h1>
    
    <!-- License Status Banner -->
    <div class="license-status-banner tier-<?php echo esc_attr($current_tier); ?>">
        <div class="license-info">
            <span class="tier-badge"><?php echo esc_html($license->get_tier_display_name($current_tier)); ?></span>
            <?php if ($current_tier === Speed_Optimizer_License::TIER_FREE): ?>
                <span class="upgrade-message">
                    <?php printf(
                        __('You\'re using the free version. <a href="%s">Upgrade now</a> to unlock all features!', 'speed-optimizer'),
                        admin_url('admin.php?page=speed-optimizer-upgrade')
                    ); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <form method="post" action="">
        <?php wp_nonce_field('speed_optimizer_settings'); ?>
        
        <div class="settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'speed-optimizer'); ?></a>
                <a href="#caching" class="nav-tab"><?php _e('Caching', 'speed-optimizer'); ?></a>
                <a href="#optimization" class="nav-tab"><?php _e('File Optimization', 'speed-optimizer'); ?></a>
                <a href="#media" class="nav-tab"><?php _e('Media', 'speed-optimizer'); ?></a>
                <a href="#ecommerce" class="nav-tab"><?php _e('eCommerce', 'speed-optimizer'); ?></a>
                <a href="#premium" class="nav-tab"><?php _e('Premium Features', 'speed-optimizer'); ?></a>
                <a href="#tools" class="nav-tab"><?php _e('Tools', 'speed-optimizer'); ?></a>
                <a href="#api" class="nav-tab"><?php _e('PageSpeed API', 'speed-optimizer'); ?></a>
                <a href="#advanced" class="nav-tab"><?php _e('Advanced', 'speed-optimizer'); ?></a>
                <a href="#import-export" class="nav-tab"><?php _e('Import/Export', 'speed-optimizer'); ?></a>
            </nav>
        </div>
        
        <!-- General Settings -->
        <div id="general" class="tab-content">
            <h2><?php _e('General Settings', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Enable Caching', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_caching" value="1" <?php checked($settings['enable_caching'], 1); ?>>
                            <?php _e('Enable browser caching with optimized headers', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Adds cache headers to improve page load times for returning visitors.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Cache Expiration', 'speed-optimizer'); ?></th>
                    <td>
                        <select name="cache_expiration">
                            <option value="3600" <?php selected($settings['cache_expiration'], 3600); ?>>1 <?php _e('hour', 'speed-optimizer'); ?></option>
                            <option value="86400" <?php selected($settings['cache_expiration'], 86400); ?>>1 <?php _e('day', 'speed-optimizer'); ?></option>
                            <option value="604800" <?php selected($settings['cache_expiration'], 604800); ?>>1 <?php _e('week', 'speed-optimizer'); ?></option>
                            <option value="2592000" <?php selected($settings['cache_expiration'], 2592000); ?>>1 <?php _e('month', 'speed-optimizer'); ?></option>
                        </select>
                        <p class="description"><?php _e('How long browsers should cache static resources.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable GZIP Compression', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_compression" value="1" <?php checked($settings['enable_compression'], 1); ?>>
                            <?php _e('Compress pages before sending to browsers', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Reduces page size by up to 70% for faster loading.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Caching Settings -->
        <div id="caching" class="tab-content" style="display: none;">
            <h2><?php _e('Caching & Performance', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Page Caching', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_page_caching" value="1" <?php checked(get_option('speed_optimizer_enable_page_caching', 1), 1); ?>>
                            <?php _e('Generate static HTML files for faster page loads', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Creates static HTML files that are served to users, reducing server load and improving response times.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('cache_preloading')): ?>
                <tr>
                    <th scope="row"><?php _e('Cache Preloading', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_cache_preloading" value="1" <?php checked(get_option('speed_optimizer_enable_cache_preloading', 0), 1); ?>>
                            <?php _e('Automatically preload cache after changes', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Ensures fresh content is always ready to serve by preloading important pages into cache.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php else: ?>
                <tr class="feature-locked">
                    <th scope="row"><?php _e('Cache Preloading', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" disabled>
                            <?php _e('Automatically preload cache after changes', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Premium feature: Ensures fresh content is always ready to serve.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Mobile Cache', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_mobile_cache" value="1" <?php checked(get_option('speed_optimizer_enable_mobile_cache', 1), 1); ?>>
                            <?php _e('Serve optimized cached content for mobile users', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Creates separate cache files for mobile devices to ensure optimal mobile experience.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('cache_logged_users')): ?>
                <tr>
                    <th scope="row"><?php _e('Cache for Logged Users', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cache_logged_users" value="1" <?php checked(get_option('speed_optimizer_cache_logged_users', 0), 1); ?>>
                            <?php _e('Enable caching for logged-in users', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Cache pages for logged-in users (use with caution for dynamic content).', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Cache Query Strings', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cache_query_strings" value="1" <?php checked(get_option('speed_optimizer_cache_query_strings', 0), 1); ?>>
                            <?php _e('Cache pages with query strings', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Enable caching for URLs with query parameters (e.g., ?utm_source=...).', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('user_agent_cache')): ?>
                <tr>
                    <th scope="row"><?php _e('User-Agent Based Cache', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="user_agent_cache" value="1" <?php checked(get_option('speed_optimizer_user_agent_cache', 0), 1); ?>>
                            <?php _e('Serve different cached content based on user agent', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Creates separate cache based on device type and browser for optimized experiences.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- File Optimization Settings -->
        <div id="optimization" class="tab-content" style="display: none;">
            <h2><?php _e('File Optimization', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Optimization Level', 'speed-optimizer'); ?></th>
                    <td>
                        <select name="optimization_level">
                            <option value="none" <?php selected($settings['optimization_level'], 'none'); ?>><?php _e('None', 'speed-optimizer'); ?></option>
                            <option value="basic" <?php selected($settings['optimization_level'], 'basic'); ?>><?php _e('Basic', 'speed-optimizer'); ?></option>
                            <option value="moderate" <?php selected($settings['optimization_level'], 'moderate'); ?>><?php _e('Moderate', 'speed-optimizer'); ?></option>
                            <option value="aggressive" <?php selected($settings['optimization_level'], 'aggressive'); ?>><?php _e('Aggressive', 'speed-optimizer'); ?></option>
                        </select>
                        <p class="description"><?php _e('Choose how aggressively to optimize your website. Higher levels may cause compatibility issues.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Minification', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_minification" value="1" <?php checked($settings['enable_minification'], 1); ?>>
                            <?php _e('Minify CSS and JavaScript files', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Removes unnecessary characters from code files to reduce size.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('file_concatenation')): ?>
                <tr>
                    <th scope="row"><?php _e('File Concatenation', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_concatenation" value="1" <?php checked(get_option('speed_optimizer_enable_concatenation', 0), 1); ?>>
                            <?php _e('Combine multiple CSS and JavaScript files', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Reduces HTTP requests by combining multiple files into one.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php else: ?>
                <tr class="feature-locked">
                    <th scope="row"><?php _e('File Concatenation', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" disabled>
                            <?php _e('Combine multiple CSS and JavaScript files', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Premium feature: Reduces HTTP requests by combining files.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($license->is_feature_available('remove_unused_css')): ?>
                <tr>
                    <th scope="row"><?php _e('Remove Unused CSS', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="remove_unused_css" value="1" <?php checked(get_option('speed_optimizer_remove_unused_css', 0), 1); ?>>
                            <?php _e('Remove CSS rules not used on the page', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Identifies and removes unused CSS to reduce file size.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($license->is_feature_available('delay_js_execution')): ?>
                <tr>
                    <th scope="row"><?php _e('Delay JavaScript Execution', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="delay_js_execution" value="1" <?php checked(get_option('speed_optimizer_delay_js_execution', 0), 1); ?>>
                            <?php _e('Delay JavaScript until user interaction', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Improves initial load time by delaying non-critical JavaScript.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($license->is_feature_available('defer_js_loading')): ?>
                <tr>
                    <th scope="row"><?php _e('Defer JavaScript Loading', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="defer_js_loading" value="1" <?php checked(get_option('speed_optimizer_defer_js_loading', 0), 1); ?>>
                            <?php _e('Load JavaScript after page content', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Defers JavaScript loading until after the main content has loaded.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <?php if ($license->is_feature_available('inline_critical_css')): ?>
                <tr>
                    <th scope="row"><?php _e('Inline Critical CSS', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="inline_critical_css" value="1" <?php checked(get_option('speed_optimizer_inline_critical_css', 0), 1); ?>>
                            <?php _e('Inject essential CSS directly into HTML', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Speeds up rendering by inlining critical CSS in the HTML head.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <!-- Media Optimization Settings -->
        <div id="media" class="tab-content" style="display: none;">
            <h2><?php _e('Media Optimization', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('LazyLoad Images', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_image_optimization" value="1" <?php checked($settings['enable_image_optimization'], 1); ?>>
                            <?php _e('Delay image loading until they enter viewport', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Images load only when they come into view, improving initial page load time.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('webp_conversion')): ?>
                <tr>
                    <th scope="row"><?php _e('WebP Conversion', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="webp_conversion" value="1" <?php checked(get_option('speed_optimizer_webp_conversion', 0), 1); ?>>
                            <?php _e('Convert images to WebP format for better compression', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically serves WebP images to supported browsers for smaller file sizes.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php else: ?>
                <tr class="feature-locked">
                    <th scope="row"><?php _e('WebP Conversion', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" disabled>
                            <?php _e('Convert images to WebP format for better compression', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Premium feature: Reduces image file sizes by up to 50%.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Image Dimensions', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="add_image_dimensions" value="1" <?php checked(get_option('speed_optimizer_add_image_dimensions', 1), 1); ?>>
                            <?php _e('Add width and height attributes to images', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Prevents layout shifts during page load by specifying image dimensions.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Disable WordPress Emoji', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="disable_emoji_script" value="1" <?php checked(get_option('speed_optimizer_disable_emoji_script', 1), 1); ?>>
                            <?php _e('Remove emoji scripts and styles', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Removes unnecessary emoji scripts to reduce HTTP requests and improve performance.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- eCommerce Optimization Settings -->
        <div id="ecommerce" class="tab-content" style="display: none;">
            <h2><?php _e('eCommerce Optimization', 'speed-optimizer'); ?></h2>
            
            <?php if (class_exists('WooCommerce')): ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('WooCommerce Optimization', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="woocommerce_optimization" value="1" <?php checked(get_option('speed_optimizer_woocommerce_optimization', 1), 1); ?>>
                                <?php _e('Enable WooCommerce performance optimizations', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Optimizes WooCommerce scripts and styles for better performance.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Exclude Cart & Checkout', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="exclude_cart_checkout" value="1" <?php checked(get_option('speed_optimizer_exclude_cart_checkout', 1), 1); ?>>
                                <?php _e('Exclude cart and checkout pages from cache', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Prevents caching of dynamic pages to ensure accurate cart and checkout content.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Cart Fragments', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="refresh_cart_fragments" value="1" <?php checked(get_option('speed_optimizer_refresh_cart_fragments', 1), 1); ?>>
                                <?php _e('Optimize cart fragment updates', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Ensures cart fragments are properly updated when cache is cleared.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                </table>
            <?php else: ?>
                <div class="notice notice-info">
                    <p><?php _e('WooCommerce is not installed. These optimizations will be available when WooCommerce is active.', 'speed-optimizer'); ?></p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Tools & Utilities Settings -->
        <div id="tools" class="tab-content" style="display: none;">
            <h2><?php _e('Tools & Utilities', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <?php if ($license->is_feature_available('heartbeat_control')): ?>
                <tr>
                    <th scope="row"><?php _e('Heartbeat Control', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="heartbeat_control" value="1" <?php checked(get_option('speed_optimizer_heartbeat_control', 0), 1); ?>>
                            <?php _e('Control WordPress Heartbeat API frequency', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Reduces server load by controlling the frequency of WordPress admin AJAX requests.', 'speed-optimizer'); ?></p>
                        
                        <label for="heartbeat_frequency" style="margin-top: 10px; display: block;">
                            <?php _e('Heartbeat Frequency (seconds):', 'speed-optimizer'); ?>
                            <select name="heartbeat_frequency" style="margin-left: 10px;">
                                <option value="15" <?php selected(get_option('speed_optimizer_heartbeat_frequency', 60), 15); ?>>15</option>
                                <option value="30" <?php selected(get_option('speed_optimizer_heartbeat_frequency', 60), 30); ?>>30</option>
                                <option value="60" <?php selected(get_option('speed_optimizer_heartbeat_frequency', 60), 60); ?>>60</option>
                                <option value="120" <?php selected(get_option('speed_optimizer_heartbeat_frequency', 60), 120); ?>>120</option>
                            </select>
                        </label>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Database Optimization', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_database_optimization" value="1" <?php checked($settings['enable_database_optimization'], 1); ?>>
                            <?php _e('Enable automatic database cleanup', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically clean spam, revisions, and optimize database tables.', 'speed-optimizer'); ?></p>
                        
                        <button type="button" id="optimize-database" class="button button-secondary" style="margin-top: 10px;">
                            <?php _e('Optimize Database Now', 'speed-optimizer'); ?>
                        </button>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Preload Links', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="preload_links" value="1" <?php checked(get_option('speed_optimizer_preload_links', 0), 1); ?>>
                            <?php _e('Preload links on hover for instant navigation', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Preloads pages when users hover over links, making navigation feel instant.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('preload_fonts')): ?>
                <tr>
                    <th scope="row"><?php _e('Preload Fonts', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="preload_fonts" value="1" <?php checked(get_option('speed_optimizer_preload_fonts', 0), 1); ?>>
                            <?php _e('Preload critical fonts', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Preloads important fonts to prevent font loading delays and layout shifts.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Preconnect Domains', 'speed-optimizer'); ?></th>
                    <td>
                        <textarea name="preconnect_domains" rows="4" class="large-text" placeholder="fonts.googleapis.com&#10;cdn.example.com"><?php echo esc_textarea(get_option('speed_optimizer_preconnect_domains', '')); ?></textarea>
                        <p class="description"><?php _e('One domain per line. Domains to establish early connections to (without https://).', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        
        <!-- Premium Features -->
        <div id="premium" class="tab-content" style="display: none;">
            <h2><?php _e('Premium Features', 'speed-optimizer'); ?></h2>
            
            <?php if ($current_tier === Speed_Optimizer_License::TIER_FREE): ?>
                <div class="premium-upgrade-notice">
                    <h3><?php _e('Unlock Advanced Performance Features', 'speed-optimizer'); ?></h3>
                    <p><?php _e('Upgrade to Premium to access these powerful optimization features:', 'speed-optimizer'); ?></p>
                    
                    <div class="premium-features-grid">
                        <div class="premium-feature">
                            <h4>🚀 <?php _e('Critical CSS Generation', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Automatically generate and inline critical CSS for faster initial page loads.', 'speed-optimizer'); ?></p>
                        </div>
                        
                        <div class="premium-feature">
                            <h4>🖼️ <?php _e('WebP Image Conversion', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Convert images to WebP format for up to 50% smaller file sizes.', 'speed-optimizer'); ?></p>
                        </div>
                        
                        <div class="premium-feature">
                            <h4>⚡ <?php _e('Advanced Caching', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Object caching, fragment caching, and advanced cache management.', 'speed-optimizer'); ?></p>
                        </div>
                        
                        <div class="premium-feature">
                            <h4>🎥 <?php _e('Video Optimization', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Lazy load videos and optimize YouTube embeds for better performance.', 'speed-optimizer'); ?></p>
                        </div>
                        
                        <div class="premium-feature">
                            <h4>🌐 <?php _e('CDN Integration', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Seamless integration with popular CDN providers.', 'speed-optimizer'); ?></p>
                        </div>
                        
                        <div class="premium-feature">
                            <h4>🔮 <?php _e('Prefetching & Preloading', 'speed-optimizer'); ?></h4>
                            <p><?php _e('Intelligently prefetch resources for instant page navigation.', 'speed-optimizer'); ?></p>
                        </div>
                    </div>
                    
                    <div class="upgrade-buttons">
                        <a href="<?php echo admin_url('admin.php?page=speed-optimizer-upgrade'); ?>" 
                           class="button button-primary button-large">
                            <?php _e('Upgrade to Premium', 'speed-optimizer'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=speed-optimizer-license'); ?>" 
                           class="button button-secondary">
                            <?php _e('I have a license key', 'speed-optimizer'); ?>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Premium Features Settings -->
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Critical CSS', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="critical_css_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_critical_css_enabled', 0), 1); ?>>
                                <?php _e('Enable critical CSS generation', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Automatically generate and inline critical CSS for faster page loads.', 'speed-optimizer'); ?></p>
                            <button type="button" class="button button-secondary" id="generate-critical-css">
                                <?php _e('Generate Critical CSS', 'speed-optimizer'); ?>
                            </button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('WebP Conversion', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="webp_conversion_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_webp_conversion_enabled', 0), 1); ?>>
                                <?php _e('Convert images to WebP format', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Automatically convert JPEG and PNG images to WebP for better compression.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Object Caching', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="object_caching_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_object_caching_enabled', 0), 1); ?>>
                                <?php _e('Enable object caching', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Cache database queries and objects for faster page generation.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Fragment Caching', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="fragment_caching_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_fragment_caching_enabled', 0), 1); ?>>
                                <?php _e('Enable fragment caching', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Cache widgets, menus, and other page fragments.', 'speed-optimizer'); ?></p>
                            <button type="button" class="button button-secondary" id="clear-fragment-cache">
                                <?php _e('Clear Fragment Cache', 'speed-optimizer'); ?>
                            </button>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Prefetching', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="prefetching_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_prefetching_enabled', 0), 1); ?>>
                                <?php _e('Enable link prefetching', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Prefetch links on hover for instant navigation.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Video Optimization', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="video_optimization_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_video_optimization_enabled', 0), 1); ?>>
                                <?php _e('Enable video optimization', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Optimize video embeds and add lazy loading.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    
                    <?php if ($license->is_feature_available('scheduled_optimization')): ?>
                    <tr>
                        <th scope="row"><?php _e('Scheduled Optimization', 'speed-optimizer'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="scheduled_optimization_enabled" value="1" 
                                       <?php checked(get_option('speed_optimizer_scheduled_optimization_enabled', 0), 1); ?>>
                                <?php _e('Enable scheduled optimization', 'speed-optimizer'); ?>
                            </label>
                            <p class="description"><?php _e('Automatically run optimization tasks daily.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </table>
            <?php endif; ?>
        </div>
        
        <!-- API Settings -->
        <div id="api" class="tab-content" style="display: none;">
            <h2><?php _e('PageSpeed Insights API', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('API Key', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="text" name="pagespeed_api_key" value="<?php echo esc_attr($settings['pagespeed_api_key']); ?>" class="regular-text">
                        <p class="description">
                            <?php _e('Get your free API key from', 'speed-optimizer'); ?> 
                            <a href="https://developers.google.com/speed/docs/insights/v5/get-started" target="_blank">Google Developers Console</a>
                        </p>
                        <?php if (!empty($settings['pagespeed_api_key'])): ?>
                        <p class="description">
                            <button type="button" id="test-api-key" class="button button-secondary">
                                <?php _e('Test API Key', 'speed-optimizer'); ?>
                            </button>
                            <span id="api-key-status"></span>
                        </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <div class="api-instructions">
                <h3><?php _e('How to get your PageSpeed Insights API Key:', 'speed-optimizer'); ?></h3>
                <ol>
                    <li><?php _e('Go to the Google Developers Console', 'speed-optimizer'); ?></li>
                    <li><?php _e('Create a new project or select an existing one', 'speed-optimizer'); ?></li>
                    <li><?php _e('Enable the PageSpeed Insights API', 'speed-optimizer'); ?></li>
                    <li><?php _e('Create credentials (API key)', 'speed-optimizer'); ?></li>
                    <li><?php _e('Copy the API key and paste it above', 'speed-optimizer'); ?></li>
                </ol>
            </div>
        </div>
        
        <!-- Advanced Settings -->
        <div id="advanced" class="tab-content" style="display: none;">
            <h2><?php _e('Advanced Settings', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('CDN URL', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="url" name="cdn_url" value="<?php echo esc_attr($settings['cdn_url']); ?>" class="regular-text" placeholder="https://cdn.example.com">
                        <p class="description"><?php _e('Optional: URL of your CDN to replace local URLs for static assets.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Exclude Files', 'speed-optimizer'); ?></th>
                    <td>
                        <textarea name="exclude_files" rows="4" class="large-text" placeholder="wp-includes/js/jquery/*&#10;wp-content/plugins/plugin-name/*"><?php echo esc_textarea($settings['exclude_files']); ?></textarea>
                        <p class="description"><?php _e('One file per line. Files to exclude from optimization (supports wildcards).', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <?php if ($license->is_feature_available('advanced_cache_rules')): ?>
                <tr>
                    <th scope="row"><?php _e('Advanced Cache Rules', 'speed-optimizer'); ?></th>
                    <td>
                        <textarea name="advanced_cache_rules" rows="6" class="large-text" placeholder="# Skip cache for admin users&#10;if (is_admin()) return false;&#10;&#10;# Skip cache for specific URLs&#10;if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) return false;"><?php echo esc_textarea(get_option('speed_optimizer_advanced_cache_rules', '')); ?></textarea>
                        <p class="description"><?php _e('Advanced PHP code to control cache behavior. Use with caution - improper code may break your site.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <?php endif; ?>
                
                <tr>
                    <th scope="row"><?php _e('Cloudflare Integration', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="cloudflare_integration" value="1" <?php checked(get_option('speed_optimizer_cloudflare_integration', 0), 1); ?>>
                            <?php _e('Enable Cloudflare compatibility mode', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Optimizes cache headers and purging for Cloudflare CDN.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Varnish Cache', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="varnish_cache" value="1" <?php checked(get_option('speed_optimizer_varnish_cache', 0), 1); ?>>
                            <?php _e('Enable Varnish cache compatibility', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Adds appropriate headers for Varnish cache servers.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Cache Management', 'speed-optimizer'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Clear Cache', 'speed-optimizer'); ?></th>
                    <td>
                        <button type="button" id="clear-all-cache" class="button button-secondary">
                            <?php _e('Clear All Cache', 'speed-optimizer'); ?>
                        </button>
                        <p class="description"><?php _e('Clear all cached files and force regeneration.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Safe Mode', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="safe_mode" value="1" <?php checked(get_option('speed_optimizer_safe_mode', 0), 1); ?>>
                            <?php _e('Enable safe mode (disables all optimizations)', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Temporarily disable all optimizations for troubleshooting.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Debug Mode', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="debug_mode" value="1" <?php checked(get_option('speed_optimizer_debug_mode', 0), 1); ?>>
                            <?php _e('Enable debug mode (adds performance comments to HTML)', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Adds HTML comments showing optimization details for debugging.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Import/Export Settings -->
        <div id="import-export" class="tab-content" style="display: none;">
            <h2><?php _e('Import/Export Settings', 'speed-optimizer'); ?></h2>
            
            <div class="export-section">
                <h3><?php _e('Export Settings', 'speed-optimizer'); ?></h3>
                <p><?php _e('Download your current plugin settings as a JSON file.', 'speed-optimizer'); ?></p>
                <button type="button" id="export-settings" class="button button-secondary">
                    <?php _e('Export Settings', 'speed-optimizer'); ?>
                </button>
            </div>
            
            <div class="import-section">
                <h3><?php _e('Import Settings', 'speed-optimizer'); ?></h3>
                <p><?php _e('Upload a settings file to restore your configuration.', 'speed-optimizer'); ?></p>
                <input type="file" id="import-file" accept=".json">
                <button type="button" id="import-settings" class="button button-secondary">
                    <?php _e('Import Settings', 'speed-optimizer'); ?>
                </button>
                <p class="description"><?php _e('Warning: This will overwrite your current settings.', 'speed-optimizer'); ?></p>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit" class="button button-primary button-large" value="<?php _e('Save Settings', 'speed-optimizer'); ?>">
        </p>
    </form>
</div>

<style>
.license-status-banner {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    margin: 20px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.license-status-banner.tier-free {
    background: linear-gradient(135deg, #666 0%, #888 100%);
}

.tier-badge {
    background: rgba(255,255,255,0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: bold;
    margin-right: 15px;
}

.upgrade-message a {
    color: #fff;
    text-decoration: underline;
}

.premium-upgrade-notice {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 30px;
    text-align: center;
}

.premium-features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin: 30px 0;
}

.premium-feature {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: left;
}

.premium-feature h4 {
    margin: 0 0 10px 0;
    color: #333;
}

.premium-feature p {
    margin: 0;
    color: #666;
    line-height: 1.5;
}

.upgrade-buttons {
    margin-top: 30px;
}

.upgrade-buttons .button {
    margin: 0 10px;
    padding: 12px 30px;
    font-size: 16px;
}

.feature-locked {
    opacity: 0.6;
    position: relative;
}

.feature-locked::after {
    content: "🔒 Premium Feature";
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ff6b35;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: bold;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show target content
        $('.tab-content').hide();
        $(target).show();
    });
    
    // Clear all cache
    $('#clear-all-cache').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Clearing...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_clear_cache',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Cache cleared successfully!', 'speed-optimizer'); ?>');
                } else {
                    alert('<?php _e('Error clearing cache', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error clearing cache', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Clear All Cache', 'speed-optimizer'); ?>');
            }
        });
    });
    
    // Optimize database
    $('#optimize-database').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Optimizing...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'run_database_optimization',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var message = '<?php _e('Database optimized successfully!', 'speed-optimizer'); ?>\n';
                    if (response.data.transients) {
                        message += '<?php _e('Expired transients removed:', 'speed-optimizer'); ?> ' + response.data.transients + '\n';
                    }
                    if (response.data.postmeta) {
                        message += '<?php _e('Orphaned postmeta removed:', 'speed-optimizer'); ?> ' + response.data.postmeta + '\n';
                    }
                    if (response.data.spam_comments) {
                        message += '<?php _e('Spam comments removed:', 'speed-optimizer'); ?> ' + response.data.spam_comments + '\n';
                    }
                    if (response.data.tables) {
                        message += '<?php _e('Tables optimized:', 'speed-optimizer'); ?> ' + response.data.tables;
                    }
                    alert(message);
                } else {
                    alert('<?php _e('Error optimizing database', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error optimizing database', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Optimize Database Now', 'speed-optimizer'); ?>');
            }
        });
    });
    
    // Premium feature buttons
    $('#generate-critical-css').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Generating...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_generate_critical_css',
                url: '<?php echo home_url(); ?>',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Critical CSS generated successfully!', 'speed-optimizer'); ?>');
                } else {
                    alert('<?php _e('Error generating critical CSS', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error generating critical CSS', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Generate Critical CSS', 'speed-optimizer'); ?>');
            }
        });
    });
    
    $('#clear-fragment-cache').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Clearing...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_clear_fragment_cache',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Fragment cache cleared successfully!', 'speed-optimizer'); ?>');
                } else {
                    alert('<?php _e('Error clearing fragment cache', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error clearing fragment cache', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Clear Fragment Cache', 'speed-optimizer'); ?>');
            }
        });
    });
    
    // Test API key
    $('#test-api-key').on('click', function() {
        var button = $(this);
        var apiKey = $('input[name="pagespeed_api_key"]').val();
        var statusSpan = $('#api-key-status');
        
        if (!apiKey) {
            statusSpan.html('<span style="color: red;">Please enter an API key first</span>');
            return;
        }
        
        button.prop('disabled', true).text('<?php _e('Testing...', 'speed-optimizer'); ?>');
        statusSpan.html('<span style="color: blue;">Testing...</span>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_test_api_key',
                api_key: apiKey,
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    statusSpan.html('<span style="color: green;">✓ API key is valid</span>');
                } else {
                    statusSpan.html('<span style="color: red;">✗ API key is invalid</span>');
                }
            },
            error: function() {
                statusSpan.html('<span style="color: red;">✗ Error testing API key</span>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Test API Key', 'speed-optimizer'); ?>');
            }
        });
    });
    
    // Export settings
    $('#export-settings').on('click', function() {
        var button = $(this);
        button.prop('disabled', true).text('<?php _e('Exporting...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_export_settings',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data));
                    var downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href", dataStr);
                    downloadAnchorNode.setAttribute("download", "speed-optimizer-settings.json");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                } else {
                    alert('<?php _e('Error exporting settings', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error exporting settings', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Export Settings', 'speed-optimizer'); ?>');
            }
        });
    });
    
    // Import settings
    $('#import-settings').on('click', function() {
        var file = $('#import-file')[0].files[0];
        if (!file) {
            alert('<?php _e('Please select a file to import', 'speed-optimizer'); ?>');
            return;
        }
        
        var reader = new FileReader();
        reader.onload = function(e) {
            try {
                var settings = JSON.parse(e.target.result);
                
                if (confirm('<?php _e('This will overwrite your current settings. Are you sure?', 'speed-optimizer'); ?>')) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'speed_optimizer_import_settings',
                            settings_data: JSON.stringify(settings),
                            nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('<?php _e('Settings imported successfully! Page will reload.', 'speed-optimizer'); ?>');
                                location.reload();
                            } else {
                                alert('<?php _e('Error importing settings', 'speed-optimizer'); ?>');
                            }
                        },
                        error: function() {
                            alert('<?php _e('Error importing settings', 'speed-optimizer'); ?>');
                        }
                    });
                }
            } catch (error) {
                alert('<?php _e('Invalid settings file', 'speed-optimizer'); ?>');
            }
        };
        reader.readAsText(file);
    });
});
</script>