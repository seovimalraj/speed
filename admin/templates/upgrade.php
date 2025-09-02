<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$fastspring = new Speed_Optimizer_FastSpring();
$plans = $fastspring->get_plans();
$license = new Speed_Optimizer_License();
$current_tier = $license->get_license_tier();
?>

<div class="wrap speed-optimizer-upgrade">
    <h1><?php _e('Upgrade Speed Optimizer', 'speed-optimizer'); ?></h1>
    
    <?php if ($current_tier !== Speed_Optimizer_License::TIER_FREE): ?>
    <div class="notice notice-info">
        <p>
            <?php printf(
                __('You currently have the %s plan. You can upgrade to a higher tier below.', 'speed-optimizer'),
                '<strong>' . $license->get_tier_display_name($current_tier) . '</strong>'
            ); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="upgrade-hero">
        <h2><?php _e('Supercharge Your WordPress Site Performance', 'speed-optimizer'); ?></h2>
        <p class="lead"><?php _e('Unlock advanced optimization features with Speed Optimizer Pro and boost your site speed by up to 300%.', 'speed-optimizer'); ?></p>
    </div>
    
    <div class="pricing-plans">
        <div class="pricing-grid">
            
            <!-- Premium Plan -->
            <div class="pricing-card premium-plan <?php echo $current_tier === Speed_Optimizer_License::TIER_PREMIUM ? 'current-plan' : ''; ?>">
                <div class="plan-header">
                    <h3><?php _e('Premium', 'speed-optimizer'); ?></h3>
                    <div class="plan-price">
                        <span class="price-monthly">$9.99<small>/month</small></span>
                        <span class="price-yearly" style="display:none;">$99.99<small>/year</small></span>
                    </div>
                    <div class="plan-billing">
                        <label class="billing-toggle">
                            <input type="radio" name="premium_billing" value="monthly" checked>
                            <?php _e('Monthly', 'speed-optimizer'); ?>
                        </label>
                        <label class="billing-toggle">
                            <input type="radio" name="premium_billing" value="yearly">
                            <?php _e('Yearly', 'speed-optimizer'); ?> <span class="save-badge"><?php _e('Save 17%', 'speed-optimizer'); ?></span>
                        </label>
                    </div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li>✓ <?php _e('All Free features', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Advanced caching mechanisms', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Critical CSS generation', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('WebP image conversion', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Video optimization', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('CDN integration', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Prefetching & preloading', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Priority support', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Advanced analytics', 'speed-optimizer'); ?></li>
                    </ul>
                </div>
                <div class="plan-action">
                    <?php if ($current_tier === Speed_Optimizer_License::TIER_PREMIUM): ?>
                        <button class="button button-secondary button-large" disabled>
                            <?php _e('Current Plan', 'speed-optimizer'); ?>
                        </button>
                    <?php else: ?>
                        <button class="button button-primary button-large upgrade-btn" 
                                data-plan="premium_monthly">
                            <?php _e('Upgrade to Premium', 'speed-optimizer'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Business Plan -->
            <div class="pricing-card business-plan popular <?php echo $current_tier === Speed_Optimizer_License::TIER_BUSINESS ? 'current-plan' : ''; ?>">
                <div class="popular-badge"><?php _e('Most Popular', 'speed-optimizer'); ?></div>
                <div class="plan-header">
                    <h3><?php _e('Business', 'speed-optimizer'); ?></h3>
                    <div class="plan-price">
                        <span class="price-monthly">$29.99<small>/month</small></span>
                        <span class="price-yearly" style="display:none;">$299.99<small>/year</small></span>
                    </div>
                    <div class="plan-billing">
                        <label class="billing-toggle">
                            <input type="radio" name="business_billing" value="monthly" checked>
                            <?php _e('Monthly', 'speed-optimizer'); ?>
                        </label>
                        <label class="billing-toggle">
                            <input type="radio" name="business_billing" value="yearly">
                            <?php _e('Yearly', 'speed-optimizer'); ?> <span class="save-badge"><?php _e('Save 17%', 'speed-optimizer'); ?></span>
                        </label>
                    </div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li>✓ <?php _e('All Premium features', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Multisite support', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Scheduled optimization', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Advanced reporting', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Up to 10 sites', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Phone support', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Performance monitoring', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Database optimization scheduling', 'speed-optimizer'); ?></li>
                    </ul>
                </div>
                <div class="plan-action">
                    <?php if ($current_tier === Speed_Optimizer_License::TIER_BUSINESS): ?>
                        <button class="button button-secondary button-large" disabled>
                            <?php _e('Current Plan', 'speed-optimizer'); ?>
                        </button>
                    <?php else: ?>
                        <button class="button button-primary button-large upgrade-btn" 
                                data-plan="business_monthly">
                            <?php _e('Upgrade to Business', 'speed-optimizer'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Agency Plan -->
            <div class="pricing-card agency-plan <?php echo $current_tier === Speed_Optimizer_License::TIER_AGENCY ? 'current-plan' : ''; ?>">
                <div class="plan-header">
                    <h3><?php _e('Agency', 'speed-optimizer'); ?></h3>
                    <div class="plan-price">
                        <span class="price-monthly">$99.99<small>/month</small></span>
                        <span class="price-yearly" style="display:none;">$999.99<small>/year</small></span>
                    </div>
                    <div class="plan-billing">
                        <label class="billing-toggle">
                            <input type="radio" name="agency_billing" value="monthly" checked>
                            <?php _e('Monthly', 'speed-optimizer'); ?>
                        </label>
                        <label class="billing-toggle">
                            <input type="radio" name="agency_billing" value="yearly">
                            <?php _e('Yearly', 'speed-optimizer'); ?> <span class="save-badge"><?php _e('Save 17%', 'speed-optimizer'); ?></span>
                        </label>
                    </div>
                </div>
                <div class="plan-features">
                    <ul>
                        <li>✓ <?php _e('All Business features', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('White-labeling', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Custom branding', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Client management', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Unlimited sites', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Dedicated support', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('White-label reporting', 'speed-optimizer'); ?></li>
                        <li>✓ <?php _e('Reseller licensing', 'speed-optimizer'); ?></li>
                    </ul>
                </div>
                <div class="plan-action">
                    <?php if ($current_tier === Speed_Optimizer_License::TIER_AGENCY): ?>
                        <button class="button button-secondary button-large" disabled>
                            <?php _e('Current Plan', 'speed-optimizer'); ?>
                        </button>
                    <?php else: ?>
                        <button class="button button-primary button-large upgrade-btn" 
                                data-plan="agency_monthly">
                            <?php _e('Upgrade to Agency', 'speed-optimizer'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>
    
    <div class="upgrade-features">
        <h2><?php _e('Why Upgrade?', 'speed-optimizer'); ?></h2>
        
        <div class="features-grid">
            <div class="feature-item">
                <div class="feature-icon">🚀</div>
                <h3><?php _e('Up to 300% Faster', 'speed-optimizer'); ?></h3>
                <p><?php _e('Advanced caching and optimization techniques can dramatically improve your site speed.', 'speed-optimizer'); ?></p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">📱</div>
                <h3><?php _e('Better Mobile Performance', 'speed-optimizer'); ?></h3>
                <p><?php _e('Critical CSS and advanced optimization ensure your site loads instantly on mobile devices.', 'speed-optimizer'); ?></p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">🎯</div>
                <h3><?php _e('Improved SEO Rankings', 'speed-optimizer'); ?></h3>
                <p><?php _e('Google rewards fast sites with better search rankings. Speed is a direct ranking factor.', 'speed-optimizer'); ?></p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">💰</div>
                <h3><?php _e('Higher Conversions', 'speed-optimizer'); ?></h3>
                <p><?php _e('Studies show that faster sites have significantly higher conversion rates and lower bounce rates.', 'speed-optimizer'); ?></p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">🔧</div>
                <h3><?php _e('Advanced Features', 'speed-optimizer'); ?></h3>
                <p><?php _e('WebP conversion, critical CSS, object caching, and more advanced optimization techniques.', 'speed-optimizer'); ?></p>
            </div>
            
            <div class="feature-item">
                <div class="feature-icon">🛟</div>
                <h3><?php _e('Priority Support', 'speed-optimizer'); ?></h3>
                <p><?php _e('Get help from our speed optimization experts when you need it most.', 'speed-optimizer'); ?></p>
            </div>
        </div>
    </div>
    
    <div class="money-back-guarantee">
        <h3><?php _e('30-Day Money-Back Guarantee', 'speed-optimizer'); ?></h3>
        <p><?php _e('Try Speed Optimizer Pro risk-free. If you\'re not satisfied, we\'ll refund your purchase within 30 days.', 'speed-optimizer'); ?></p>
    </div>
    
</div>

<style>
.upgrade-hero {
    text-align: center;
    padding: 40px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    margin: 20px 0;
}

.upgrade-hero h2 {
    font-size: 2.5em;
    margin-bottom: 10px;
}

.upgrade-hero .lead {
    font-size: 1.2em;
    opacity: 0.9;
}

.pricing-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 40px 0;
}

.pricing-card {
    background: white;
    border: 2px solid #e1e5e9;
    border-radius: 12px;
    padding: 30px;
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.pricing-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.pricing-card.popular {
    border-color: #0073aa;
    transform: scale(1.05);
}

.pricing-card.current-plan {
    border-color: #00a32a;
    background: #f0f8f0;
}

.popular-badge {
    position: absolute;
    top: -15px;
    left: 50%;
    transform: translateX(-50%);
    background: #0073aa;
    color: white;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: bold;
}

.plan-header h3 {
    font-size: 1.8em;
    margin-bottom: 10px;
    text-align: center;
}

.plan-price {
    text-align: center;
    margin-bottom: 20px;
}

.plan-price .price-monthly,
.plan-price .price-yearly {
    font-size: 2.5em;
    font-weight: bold;
    color: #333;
}

.plan-price small {
    font-size: 0.4em;
    color: #666;
}

.plan-billing {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 20px;
}

.billing-toggle {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 14px;
}

.billing-toggle input {
    margin-right: 5px;
}

.save-badge {
    background: #00a32a;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    margin-left: 5px;
}

.plan-features ul {
    list-style: none;
    padding: 0;
    margin: 20px 0;
}

.plan-features li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
}

.plan-features li:last-child {
    border-bottom: none;
}

.plan-action {
    text-align: center;
    margin-top: 20px;
}

.plan-action .button-large {
    width: 100%;
    padding: 15px;
    font-size: 16px;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin: 40px 0;
}

.feature-item {
    text-align: center;
    padding: 20px;
}

.feature-icon {
    font-size: 3em;
    margin-bottom: 15px;
}

.feature-item h3 {
    margin-bottom: 10px;
    color: #333;
}

.money-back-guarantee {
    text-align: center;
    background: #f8f9fa;
    padding: 30px;
    border-radius: 8px;
    margin: 40px 0;
}

.money-back-guarantee h3 {
    color: #00a32a;
    margin-bottom: 10px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle billing period toggle
    $('input[name$="_billing"]').on('change', function() {
        var card = $(this).closest('.pricing-card');
        var period = $(this).val();
        
        if (period === 'yearly') {
            card.find('.price-monthly').hide();
            card.find('.price-yearly').show();
        } else {
            card.find('.price-yearly').hide();
            card.find('.price-monthly').show();
        }
        
        // Update button data-plan
        var planType = card.find('.upgrade-btn').data('plan').split('_')[0];
        card.find('.upgrade-btn').data('plan', planType + '_' + period);
    });
    
    // Handle upgrade button clicks
    $('.upgrade-btn').on('click', function() {
        var planId = $(this).data('plan');
        var button = $(this);
        
        // Show loading state
        button.prop('disabled', true).text('<?php _e('Processing...', 'speed-optimizer'); ?>');
        
        // Create checkout session
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'speed_optimizer_create_checkout',
                plan_id: planId,
                email: '<?php echo get_option('admin_email'); ?>',
                first_name: '',
                last_name: '',
                nonce: '<?php echo wp_create_nonce('speed_optimizer_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    // Open FastSpring checkout
                    window.open(response.data.checkout_url, '_blank', 'width=800,height=600');
                } else {
                    alert('<?php _e('Error creating checkout session', 'speed-optimizer'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error creating checkout session', 'speed-optimizer'); ?>');
            },
            complete: function() {
                button.prop('disabled', false).text('<?php _e('Upgrade Now', 'speed-optimizer'); ?>');
            }
        });
    });
});
</script>