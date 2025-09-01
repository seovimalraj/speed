<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin = new Speed_Optimizer_Admin();
$settings = $admin->get_settings();

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
    
    <form method="post" action="">
        <?php wp_nonce_field('speed_optimizer_settings'); ?>
        
        <div class="settings-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('General', 'speed-optimizer'); ?></a>
                <a href="#optimization" class="nav-tab"><?php _e('Optimization', 'speed-optimizer'); ?></a>
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