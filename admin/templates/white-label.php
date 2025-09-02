<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$license = new Speed_Optimizer_License();

if (!$license->is_feature_available('white_labeling')) {
    wp_die(__('White labeling requires an Agency license.', 'speed-optimizer'));
}

$white_label = new Speed_Optimizer_White_Label();
$settings = $white_label->get_white_label_settings();

// Handle form submission
if (isset($_POST['submit_white_label']) && wp_verify_nonce($_POST['_wpnonce'], 'speed_optimizer_white_label')) {
    $white_label->save_white_label_settings($_POST);
    echo '<div class="notice notice-success"><p>' . __('White label settings saved successfully!', 'speed-optimizer') . '</p></div>';
    $settings = $white_label->get_white_label_settings();
}
?>

<div class="wrap speed-optimizer-white-label">
    <h1><?php _e('White Label Settings', 'speed-optimizer'); ?></h1>
    
    <div class="white-label-intro">
        <p><?php _e('Customize the plugin appearance and branding to match your agency or company identity.', 'speed-optimizer'); ?></p>
    </div>
    
    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('speed_optimizer_white_label'); ?>
        
        <div class="white-label-tabs">
            <nav class="nav-tab-wrapper">
                <a href="#branding" class="nav-tab nav-tab-active"><?php _e('Branding', 'speed-optimizer'); ?></a>
                <a href="#appearance" class="nav-tab"><?php _e('Appearance', 'speed-optimizer'); ?></a>
                <a href="#support" class="nav-tab"><?php _e('Support', 'speed-optimizer'); ?></a>
                <a href="#reports" class="nav-tab"><?php _e('Client Reports', 'speed-optimizer'); ?></a>
            </nav>
        </div>
        
        <!-- Branding Tab -->
        <div id="branding" class="tab-content">
            <h2><?php _e('Plugin Branding', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Plugin Name', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="text" name="plugin_name" value="<?php echo esc_attr($settings['plugin_name']); ?>" class="regular-text">
                        <p class="description"><?php _e('Custom name for the plugin (shown in admin menu)', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Plugin Description', 'speed-optimizer'); ?></th>
                    <td>
                        <textarea name="plugin_description" rows="3" class="large-text"><?php echo esc_textarea($settings['plugin_description']); ?></textarea>
                        <p class="description"><?php _e('Custom description for the plugin', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Company Name', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="text" name="company_name" value="<?php echo esc_attr($settings['company_name']); ?>" class="regular-text">
                        <p class="description"><?php _e('Your company or agency name', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Company Logo', 'speed-optimizer'); ?></th>
                    <td>
                        <div class="logo-upload-section">
                            <?php if (!empty($settings['company_logo'])): ?>
                                <div class="current-logo">
                                    <img src="<?php echo esc_url($settings['company_logo']); ?>" alt="Company Logo" style="max-width: 200px; height: auto;">
                                    <p><button type="button" class="button remove-logo"><?php _e('Remove Logo', 'speed-optimizer'); ?></button></p>
                                </div>
                            <?php endif; ?>
                            
                            <input type="hidden" name="company_logo" value="<?php echo esc_attr($settings['company_logo']); ?>" id="company-logo-url">
                            <button type="button" class="button upload-logo"><?php _e('Upload Logo', 'speed-optimizer'); ?></button>
                            <p class="description"><?php _e('Upload your company logo (recommended size: 32x32px)', 'speed-optimizer'); ?></p>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Company Website', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="url" name="company_url" value="<?php echo esc_attr($settings['company_url']); ?>" class="regular-text">
                        <p class="description"><?php _e('Your company website URL', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Hide Original Branding', 'speed-optimizer'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="hide_original_branding" value="1" <?php checked($settings['hide_original_branding'], 1); ?>>
                            <?php _e('Hide all references to the original plugin author', 'speed-optimizer'); ?>
                        </label>
                        <p class="description"><?php _e('Enable this to completely white-label the plugin', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Appearance Tab -->
        <div id="appearance" class="tab-content" style="display: none;">
            <h2><?php _e('Custom Appearance', 'speed-optimizer'); ?></h2>
            
            <h3><?php _e('Color Scheme', 'speed-optimizer'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Primary Color', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="color" name="custom_colors[primary]" value="<?php echo esc_attr($settings['custom_colors']['primary'] ?? '#0073aa'); ?>" class="color-picker">
                        <p class="description"><?php _e('Main color for buttons and highlights', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Secondary Color', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="color" name="custom_colors[secondary]" value="<?php echo esc_attr($settings['custom_colors']['secondary'] ?? '#666666'); ?>" class="color-picker">
                        <p class="description"><?php _e('Secondary color for borders and text', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Accent Color', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="color" name="custom_colors[accent]" value="<?php echo esc_attr($settings['custom_colors']['accent'] ?? '#f1f1f1'); ?>" class="color-picker">
                        <p class="description"><?php _e('Accent color for backgrounds and cards', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Custom CSS', 'speed-optimizer'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Additional CSS', 'speed-optimizer'); ?></th>
                    <td>
                        <textarea name="custom_css" rows="10" class="large-text code"><?php echo esc_textarea($settings['custom_css']); ?></textarea>
                        <p class="description"><?php _e('Add custom CSS to further customize the plugin appearance', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h3><?php _e('Footer Text', 'speed-optimizer'); ?></h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Custom Footer', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="text" name="footer_text" value="<?php echo esc_attr($settings['footer_text']); ?>" class="regular-text">
                        <p class="description"><?php _e('Custom text to display in the admin footer', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Support Tab -->
        <div id="support" class="tab-content" style="display: none;">
            <h2><?php _e('Support Information', 'speed-optimizer'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('Support Email', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="email" name="support_email" value="<?php echo esc_attr($settings['support_email']); ?>" class="regular-text">
                        <p class="description"><?php _e('Email address for plugin support requests', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><?php _e('Support URL', 'speed-optimizer'); ?></th>
                    <td>
                        <input type="url" name="support_url" value="<?php echo esc_attr($settings['support_url']); ?>" class="regular-text">
                        <p class="description"><?php _e('URL for your support portal or help desk', 'speed-optimizer'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Client Reports Tab -->
        <div id="reports" class="tab-content" style="display: none;">
            <h2><?php _e('Client Reports', 'speed-optimizer'); ?></h2>
            
            <div class="client-reports-section">
                <h3><?php _e('Generate Client Report', 'speed-optimizer'); ?></h3>
                <p><?php _e('Create branded performance reports for your clients.', 'speed-optimizer'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Site URL', 'speed-optimizer'); ?></th>
                        <td>
                            <input type="url" id="report-site-url" value="<?php echo get_home_url(); ?>" class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row"><?php _e('Timeframe', 'speed-optimizer'); ?></th>
                        <td>
                            <select id="report-timeframe">
                                <option value="7_days"><?php _e('Last 7 days', 'speed-optimizer'); ?></option>
                                <option value="30_days" selected><?php _e('Last 30 days', 'speed-optimizer'); ?></option>
                                <option value="90_days"><?php _e('Last 90 days', 'speed-optimizer'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                
                <p>
                    <button type="button" id="generate-client-report" class="button button-secondary">
                        <?php _e('Generate Report', 'speed-optimizer'); ?>
                    </button>
                    <button type="button" id="download-client-report" class="button button-primary" style="display: none;">
                        <?php _e('Download PDF Report', 'speed-optimizer'); ?>
                    </button>
                </p>
                
                <div id="report-preview" style="display: none;">
                    <h3><?php _e('Report Preview', 'speed-optimizer'); ?></h3>
                    <div id="report-content"></div>
                </div>
            </div>
        </div>
        
        <p class="submit">
            <input type="submit" name="submit_white_label" class="button button-primary button-large" 
                   value="<?php _e('Save White Label Settings', 'speed-optimizer'); ?>">
        </p>
    </form>
</div>

<style>
.white-label-intro {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.white-label-tabs .nav-tab-wrapper {
    margin-bottom: 20px;
}

.tab-content {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
}

.logo-upload-section .current-logo {
    margin-bottom: 15px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #f9f9f9;
}

.color-picker {
    width: 100px;
    height: 40px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.client-reports-section {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

#report-preview {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-top: 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        
        var target = $(this).attr('href');
        
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        $('.tab-content').hide();
        $(target).show();
    });
    
    // Logo upload
    $('.upload-logo').on('click', function() {
        var mediaUploader = wp.media({
            title: '<?php _e('Select Company Logo', 'speed-optimizer'); ?>',
            button: {
                text: '<?php _e('Use This Logo', 'speed-optimizer'); ?>'
            },
            multiple: false
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#company-logo-url').val(attachment.url);
            
            var logoHtml = '<div class="current-logo">' +
                '<img src="' + attachment.url + '" alt="Company Logo" style="max-width: 200px; height: auto;">' +
                '<p><button type="button" class="button remove-logo"><?php _e('Remove Logo', 'speed-optimizer'); ?></button></p>' +
                '</div>';
            
            $('.logo-upload-section').prepend(logoHtml);
        });
        
        mediaUploader.open();
    });
    
    // Remove logo
    $(document).on('click', '.remove-logo', function() {
        $('#company-logo-url').val('');
        $('.current-logo').remove();
    });
    
    // Generate client report
    $('#generate-client-report').on('click', function() {
        var button = $(this);
        var siteUrl = $('#report-site-url').val();
        var timeframe = $('#report-timeframe').val();
        
        button.prop('disabled', true).text('<?php _e('Generating...', 'speed-optimizer'); ?>');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_generate_client_report',
                site_url: siteUrl,
                timeframe: timeframe,
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#report-content').html(response.data.html);
                    $('#report-preview').show();
                    $('#download-client-report').show();
                } else {
                    alert('<?php _e('Error generating report', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error generating report', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Generate Report', 'speed-optimizer'); ?>');
            }
        });
    });
});
</script>