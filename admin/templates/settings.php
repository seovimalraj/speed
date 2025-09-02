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
                <a href="#optimization" class="nav-tab"><?php _e('Optimization', 'speed-optimizer'); ?></a>
                <a href="#premium" class="nav-tab"><?php _e('Premium Features', 'speed-optimizer'); ?></a>
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
        
        <!-- Optimization Settings -->
        <div id="optimization" class="tab-content" style="display: none;">
            <h2><?php _e('Optimization Settings', 'speed-optimizer'); ?></h2>
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
                    <th scope="row"><?php _e('Enable Minification', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_minification" value="1" <?php checked($settings['enable_minification'], 1); ?>>
                            <?php _e('Minify CSS and JavaScript files', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Removes unnecessary characters from code files to reduce size.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Image Optimization', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_image_optimization" value="1" <?php checked($settings['enable_image_optimization'], 1); ?>>
                            <?php _e('Optimize images with lazy loading', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Images load only when they come into view, improving initial page load time.', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Enable Database Optimization', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="enable_database_optimization" value="1" <?php checked($settings['enable_database_optimization'], 1); ?>>
                            <?php _e('Enable automatic database cleanup', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Automatically clean spam, revisions, and optimize database tables.', 'speed-optimizer'); ?></p>
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
                        <textarea name="exclude_files" rows="4" class="large-text"><?php echo esc_textarea($settings['exclude_files']); ?></textarea>
                        <p class="description"><?php _e('One file per line. Files to exclude from optimization (supports wildcards).', 'speed-optimizer'); ?></p>
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
});
</script>