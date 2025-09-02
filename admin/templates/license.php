<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$license = new Speed_Optimizer_License();
$license_info = $license->get_license_info();
$current_tier = $license->get_license_tier();

// Handle form submission
if (isset($_POST['submit_license']) && wp_verify_nonce($_POST['_wpnonce'], 'speed_optimizer_license')) {
    $license_key = sanitize_text_field($_POST['license_key']);
    $result = $license->activate_license($license_key);
    
    if ($result['success']) {
        echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
        $license_info = $license->get_license_info();
        $current_tier = $license->get_license_tier();
    } else {
        echo '<div class="notice notice-error"><p>' . esc_html($result['message']) . '</p></div>';
    }
}

if (isset($_POST['deactivate_license']) && wp_verify_nonce($_POST['_wpnonce'], 'speed_optimizer_license')) {
    $result = $license->deactivate_license();
    echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
    $license_info = $license->get_license_info();
    $current_tier = $license->get_license_tier();
}
?>

<div class="wrap speed-optimizer-license">
    <h1><?php _e('Speed Optimizer License', 'speed-optimizer'); ?></h1>
    
    <div class="license-status-card">
        <div class="license-status-header">
            <h2><?php _e('License Status', 'speed-optimizer'); ?></h2>
            <div class="license-tier-badge tier-<?php echo esc_attr($current_tier); ?>">
                <?php echo esc_html($license->get_tier_display_name($current_tier)); ?>
            </div>
        </div>
        
        <div class="license-status-body">
            <?php if ($current_tier === Speed_Optimizer_License::TIER_FREE): ?>
                <div class="license-free">
                    <p><?php _e('You are currently using the free version of Speed Optimizer.', 'speed-optimizer'); ?></p>
                    <p><?php _e('Upgrade to unlock premium features like advanced caching, critical CSS generation, WebP conversion, and more.', 'speed-optimizer'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=speed-optimizer-upgrade'); ?>" class="button button-primary button-large">
                        <?php _e('Upgrade Now', 'speed-optimizer'); ?>
                    </a>
                </div>
            <?php else: ?>
                <div class="license-active">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('License Key', 'speed-optimizer'); ?></th>
                            <td>
                                <code><?php echo esc_html($license_info['key']); ?></code>
                                <p class="description"><?php _e('Your license key is active and valid.', 'speed-optimizer'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('License Tier', 'speed-optimizer'); ?></th>
                            <td>
                                <strong><?php echo esc_html($license->get_tier_display_name($current_tier)); ?></strong>
                            </td>
                        </tr>
                        <?php if (!empty($license_info['expires'])): ?>
                        <tr>
                            <th scope="row"><?php _e('Expires', 'speed-optimizer'); ?></th>
                            <td>
                                <?php echo esc_html(date('F j, Y', strtotime($license_info['expires']))); ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                    
                    <form method="post" action="">
                        <?php wp_nonce_field('speed_optimizer_license'); ?>
                        <p>
                            <input type="submit" name="deactivate_license" class="button button-secondary" 
                                   value="<?php _e('Deactivate License', 'speed-optimizer'); ?>"
                                   onclick="return confirm('<?php _e('Are you sure you want to deactivate your license?', 'speed-optimizer'); ?>');">
                        </p>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($current_tier === Speed_Optimizer_License::TIER_FREE): ?>
    <div class="license-activation-card">
        <h2><?php _e('Activate License', 'speed-optimizer'); ?></h2>
        <p><?php _e('Enter your license key to unlock premium features.', 'speed-optimizer'); ?></p>
        
        <form method="post" action="" id="license-activation-form">
            <?php wp_nonce_field('speed_optimizer_license'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="license_key"><?php _e('License Key', 'speed-optimizer'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="license_key" name="license_key" class="regular-text" 
                               placeholder="<?php _e('Enter your license key', 'speed-optimizer'); ?>" required>
                        <p class="description">
                            <?php printf(
                                __('Don\'t have a license key? <a href="%s">Purchase one here</a>.', 'speed-optimizer'),
                                admin_url('admin.php?page=speed-optimizer-upgrade')
                            ); ?>
                        </p>
                    </td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit_license" class="button button-primary" 
                       value="<?php _e('Activate License', 'speed-optimizer'); ?>">
                <span class="spinner" id="license-spinner"></span>
            </p>
        </form>
    </div>
    <?php endif; ?>
    
    <div class="feature-comparison-card">
        <h2><?php _e('Feature Comparison', 'speed-optimizer'); ?></h2>
        
        <div class="feature-comparison-table">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Feature', 'speed-optimizer'); ?></th>
                        <th class="text-center"><?php _e('Free', 'speed-optimizer'); ?></th>
                        <th class="text-center"><?php _e('Premium', 'speed-optimizer'); ?></th>
                        <th class="text-center"><?php _e('Business', 'speed-optimizer'); ?></th>
                        <th class="text-center"><?php _e('Agency', 'speed-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php _e('Basic Page Caching', 'speed-optimizer'); ?></td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('CSS/JS Minification', 'speed-optimizer'); ?></td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('GZIP Compression', 'speed-optimizer'); ?></td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Basic Image Lazy Loading', 'speed-optimizer'); ?></td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Critical CSS Generation', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('WebP Image Conversion', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Advanced Caching (Object/Fragment)', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('CDN Integration', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Video Optimization', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Prefetching & Preloading', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Priority Support', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Multisite Support', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Scheduled Optimization', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('White-labeling', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                    </tr>
                    <tr>
                        <td><?php _e('Custom Branding', 'speed-optimizer'); ?></td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">-</td>
                        <td class="text-center">✓</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.license-status-card, .license-activation-card, .feature-comparison-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin: 20px 0;
    padding: 20px;
}

.license-status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.license-tier-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: bold;
    color: #fff;
}

.license-tier-badge.tier-free {
    background: #666;
}

.license-tier-badge.tier-premium {
    background: #0073aa;
}

.license-tier-badge.tier-business {
    background: #00a32a;
}

.license-tier-badge.tier-agency {
    background: #d63384;
}

.license-free, .license-active {
    padding: 20px;
    border-radius: 4px;
}

.license-free {
    background: #f8f9fa;
    text-align: center;
}

.license-active {
    background: #e8f5e8;
}

.feature-comparison-table .text-center {
    text-align: center;
    width: 80px;
}

.spinner {
    float: none;
    margin-left: 10px;
}

#license-spinner.is-active {
    display: inline-block;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('#license-activation-form').on('submit', function() {
        $('#license-spinner').addClass('is-active');
    });
});
</script>