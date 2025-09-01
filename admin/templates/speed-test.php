<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$database = new Speed_Optimizer_Database();
$recent_tests = $database->get_speed_test_history('', 20);
?>

<div class="wrap speed-optimizer-speed-test">
    <h1><?php _e('Speed Test', 'speed-optimizer'); ?></h1>
    
    <div class="speed-test-form">
        <div class="card">
            <h2><?php _e('Run PageSpeed Insights Test', 'speed-optimizer'); ?></h2>
            <form id="speed-test-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="test-url"><?php _e('URL to Test', 'speed-optimizer'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="test-url" name="url" value="<?php echo esc_url(home_url()); ?>" class="regular-text" required>
                            <p class="description"><?php _e('Enter the URL you want to test for page speed performance.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="test-strategy"><?php _e('Test Strategy', 'speed-optimizer'); ?></label>
                        </th>
                        <td>
                            <select id="test-strategy" name="strategy">
                                <option value="both"><?php _e('Both Desktop and Mobile', 'speed-optimizer'); ?></option>
                                <option value="desktop"><?php _e('Desktop Only', 'speed-optimizer'); ?></option>
                                <option value="mobile"><?php _e('Mobile Only', 'speed-optimizer'); ?></option>
                            </select>
                            <p class="description"><?php _e('Choose which device types to test.', 'speed-optimizer'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        <span class="dashicons dashicons-performance"></span>
                        <?php _e('Run Speed Test', 'speed-optimizer'); ?>
                    </button>
                </p>
            </form>
        </div>
    </div>
    
    <div id="test-progress" style="display: none;">
        <div class="card">
            <h2><?php _e('Testing in Progress...', 'speed-optimizer'); ?></h2>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
            <p><?php _e('Please wait while we analyze your page speed. This may take up to 60 seconds.', 'speed-optimizer'); ?></p>
        </div>
    </div>
    
    <div id="test-results" style="display: none;">
        <div class="card">
            <h2><?php _e('Test Results', 'speed-optimizer'); ?></h2>
            <div id="results-content"></div>
        </div>
    </div>
    
    <div class="speed-test-history">
        <div class="card">
            <h2><?php _e('Speed Test History', 'speed-optimizer'); ?></h2>
            <?php if (!empty($recent_tests)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('URL', 'speed-optimizer'); ?></th>
                        <th><?php _e('Desktop Score', 'speed-optimizer'); ?></th>
                        <th><?php _e('Mobile Score', 'speed-optimizer'); ?></th>
                        <th><?php _e('Desktop FCP', 'speed-optimizer'); ?></th>
                        <th><?php _e('Mobile FCP', 'speed-optimizer'); ?></th>
                        <th><?php _e('Test Date', 'speed-optimizer'); ?></th>
                        <th><?php _e('Actions', 'speed-optimizer'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_tests as $test): ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url($test->url); ?>" target="_blank">
                                <?php echo esc_html(parse_url($test->url, PHP_URL_PATH) ?: '/'); ?>
                            </a>
                        </td>
                        <td>
                            <?php if ($test->desktop_score): ?>
                            <span class="score-badge score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color($test->desktop_score); ?>">
                                <?php echo $test->desktop_score; ?>
                            </span>
                            <?php else: ?>
                            <span class="score-badge score-na">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($test->mobile_score): ?>
                            <span class="score-badge score-<?php echo Speed_Optimizer_PageSpeed_API::get_score_color($test->mobile_score); ?>">
                                <?php echo $test->mobile_score; ?>
                            </span>
                            <?php else: ?>
                            <span class="score-badge score-na">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $test->desktop_fcp ? number_format($test->desktop_fcp, 2) . 's' : '-'; ?>
                        </td>
                        <td>
                            <?php echo $test->mobile_fcp ? number_format($test->mobile_fcp, 2) . 's' : '-'; ?>
                        </td>
                        <td>
                            <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($test->test_date)); ?>
                        </td>
                        <td>
                            <button type="button" class="button button-small view-details" data-test-id="<?php echo $test->id; ?>">
                                <?php _e('View Details', 'speed-optimizer'); ?>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="no-tests">
                <p><?php _e('No speed tests found.', 'speed-optimizer'); ?></p>
                <p><?php _e('Run your first speed test using the form above to start tracking your website performance.', 'speed-optimizer'); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Test Details Modal -->
<div id="test-details-modal" style="display: none;">
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3><?php _e('Test Details', 'speed-optimizer'); ?></h3>
                <button type="button" class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div id="test-details-content">
                    <?php _e('Loading...', 'speed-optimizer'); ?>
                </div>
            </div>
        </div>
    </div>
</div>