<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin = new Speed_Optimizer_Admin();
$dashboard_data = $admin->get_dashboard_data();
$stats = $dashboard_data['stats'];
$recent_tests = $dashboard_data['recent_tests'];
$recent_logs = $dashboard_data['recent_logs'];
?>

<div class="wrap speed-optimizer-dashboard">
    <h1><?php _e('Speed Optimizer Dashboard', 'speed-optimizer'); ?></h1>
    
    <?php if (!$dashboard_data['has_api_key']): ?>
    <div class="notice notice-warning">
        <p>
            <?php _e('PageSpeed Insights API key is not configured.', 'speed-optimizer'); ?>
            <a href="<?php echo admin_url('admin.php?page=speed-optimizer-settings'); ?>"><?php _e('Configure it now', 'speed-optimizer'); ?></a>
        </p>
    </div>
    <?php endif; ?>
    
    <div class="speed-optimizer-stats">
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php _e('Total Tests', 'speed-optimizer'); ?></h3>
                <div class="stat-number"><?php echo intval($stats['total_tests']); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><?php _e('Avg Desktop Score', 'speed-optimizer'); ?></h3>
                <div class="stat-number score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color(intval($stats['avg_desktop_score'])); ?>">
                    <?php echo intval($stats['avg_desktop_score']); ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3><?php _e('Avg Mobile Score', 'speed-optimizer'); ?></h3>
                <div class="stat-number score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color(intval($stats['avg_mobile_score'])); ?>">
                    <?php echo intval($stats['avg_mobile_score']); ?>
                </div>
            </div>
            
            <div class="stat-card">
                <h3><?php _e('Recent Tests (7 days)', 'speed-optimizer'); ?></h3>
                <div class="stat-number"><?php echo intval($stats['recent_tests']); ?></div>
            </div>
        </div>
    </div>
    
    <div class="speed-optimizer-content">
        <div class="content-left">
            <div class="card">
                <h2><?php _e('Quick Speed Test', 'speed-optimizer'); ?></h2>
                <form id="quick-speed-test">
                    <div class="form-group">
                        <label for="test-url"><?php _e('URL to Test:', 'speed-optimizer'); ?></label>
                        <input type="url" id="test-url" value="<?php echo esc_url($dashboard_data['site_url']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="test-strategy"><?php _e('Strategy:', 'speed-optimizer'); ?></label>
                        <select id="test-strategy">
                            <option value="both"><?php _e('Both (Desktop & Mobile)', 'speed-optimizer'); ?></option>
                            <option value="desktop"><?php _e('Desktop Only', 'speed-optimizer'); ?></option>
                            <option value="mobile"><?php _e('Mobile Only', 'speed-optimizer'); ?></option>
                        </select>
                    </div>
                    <button type="submit" class="button button-primary">
                        <?php _e('Run Speed Test', 'speed-optimizer'); ?>
                    </button>
                </form>
                
                <div id="test-results" style="display: none;">
                    <h3><?php _e('Test Results', 'speed-optimizer'); ?></h3>
                    <div id="results-content"></div>
                </div>
            </div>
            
            <div class="card">
                <h2><?php _e('Recent Speed Tests', 'speed-optimizer'); ?></h2>
                <?php if (!empty($recent_tests)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('URL', 'speed-optimizer'); ?></th>
                            <th><?php _e('Desktop', 'speed-optimizer'); ?></th>
                            <th><?php _e('Mobile', 'speed-optimizer'); ?></th>
                            <th><?php _e('Date', 'speed-optimizer'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_tests as $test): ?>
                        <tr>
                            <td><?php echo esc_html($test->url); ?></td>
                            <td>
                                <span class="score score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color($test->desktop_score); ?>">
                                    <?php echo $test->desktop_score ? $test->desktop_score : '-'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="score score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color($test->mobile_score); ?>">
                                    <?php echo $test->mobile_score ? $test->mobile_score : '-'; ?>
                                </span>
                            </td>
                            <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($test->test_date)); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p><?php _e('No speed tests found. Run your first test above!', 'speed-optimizer'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="content-right">
            <div class="card">
                <h2><?php _e('Quick Actions', 'speed-optimizer'); ?></h2>
                <div class="quick-actions">
                    <button type="button" class="button" id="clear-cache">
                        <?php _e('Clear Cache', 'speed-optimizer'); ?>
                    </button>
                    <button type="button" class="button" id="optimize-database">
                        <?php _e('Optimize Database', 'speed-optimizer'); ?>
                    </button>
                    <a href="<?php echo admin_url('admin.php?page=speed-optimizer-settings'); ?>" class="button">
                        <?php _e('Settings', 'speed-optimizer'); ?>
                    </a>
                </div>
            </div>
            
            <div class="card">
                <h2><?php _e('Recent Activity', 'speed-optimizer'); ?></h2>
                <?php if (!empty($recent_logs)): ?>
                <ul class="activity-log">
                    <?php foreach ($recent_logs as $log): ?>
                    <li class="activity-<?php echo esc_attr($log->status); ?>">
                        <strong><?php echo esc_html($log->action); ?></strong>
                        <?php if ($log->description): ?>
                        <p><?php echo esc_html($log->description); ?></p>
                        <?php endif; ?>
                        <small><?php echo human_time_diff(strtotime($log->log_date), current_time('timestamp')); ?> <?php _e('ago', 'speed-optimizer'); ?></small>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php else: ?>
                <p><?php _e('No recent activity.', 'speed-optimizer'); ?></p>
                <?php endif; ?>
            </div>
            
            <div class="card">
                <h2><?php _e('Optimization Tips', 'speed-optimizer'); ?></h2>
                <ul class="optimization-tips">
                    <li><?php _e('Enable caching for faster page loads', 'speed-optimizer'); ?></li>
                    <li><?php _e('Optimize images to reduce file sizes', 'speed-optimizer'); ?></li>
                    <li><?php _e('Minify CSS and JavaScript files', 'speed-optimizer'); ?></li>
                    <li><?php _e('Use a CDN for global content delivery', 'speed-optimizer'); ?></li>
                    <li><?php _e('Regular database optimization improves performance', 'speed-optimizer'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>