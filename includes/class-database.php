<?php
/**
 * Database class for Speed Optimizer plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_Database {
    
    private $charset_collate;
    
    public function __construct() {
        global $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
    }
    
    /**
     * Create plugin database tables
     */
    public function create_tables() {
        global $wpdb;
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Create speed tests table
        $table_name = $wpdb->prefix . 'speed_optimizer_tests';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            url varchar(255) NOT NULL,
            desktop_score int(3) DEFAULT NULL,
            mobile_score int(3) DEFAULT NULL,
            desktop_fcp float DEFAULT NULL,
            desktop_lcp float DEFAULT NULL,
            mobile_fcp float DEFAULT NULL,
            mobile_lcp float DEFAULT NULL,
            test_date datetime DEFAULT CURRENT_TIMESTAMP,
            raw_data longtext,
            PRIMARY KEY (id),
            KEY url_index (url),
            KEY date_index (test_date)
        ) $this->charset_collate;";
        
        dbDelta($sql);
        
        // Create optimization logs table
        $table_name = $wpdb->prefix . 'speed_optimizer_logs';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            action varchar(100) NOT NULL,
            description text,
            status varchar(20) DEFAULT 'success',
            log_date datetime DEFAULT CURRENT_TIMESTAMP,
            user_id bigint(20) DEFAULT NULL,
            PRIMARY KEY (id),
            KEY action_index (action),
            KEY date_index (log_date),
            KEY user_index (user_id)
        ) $this->charset_collate;";
        
        dbDelta($sql);
    }
    
    /**
     * Save speed test result
     */
    public function save_speed_test($url, $desktop_data, $mobile_data, $raw_data = '') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_tests';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'url' => $url,
                'desktop_score' => isset($desktop_data['score']) ? $desktop_data['score'] : null,
                'mobile_score' => isset($mobile_data['score']) ? $mobile_data['score'] : null,
                'desktop_fcp' => isset($desktop_data['fcp']) ? $desktop_data['fcp'] : null,
                'desktop_lcp' => isset($desktop_data['lcp']) ? $desktop_data['lcp'] : null,
                'mobile_fcp' => isset($mobile_data['fcp']) ? $mobile_data['fcp'] : null,
                'mobile_lcp' => isset($mobile_data['lcp']) ? $mobile_data['lcp'] : null,
                'raw_data' => $raw_data,
                'test_date' => current_time('mysql')
            ),
            array(
                '%s', '%d', '%d', '%f', '%f', '%f', '%f', '%s', '%s'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get speed test history
     */
    public function get_speed_test_history($url = '', $limit = 10) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_tests';
        
        if ($url) {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name WHERE url = %s ORDER BY test_date DESC LIMIT %d",
                $url,
                $limit
            );
        } else {
            $sql = $wpdb->prepare(
                "SELECT * FROM $table_name ORDER BY test_date DESC LIMIT %d",
                $limit
            );
        }
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Log optimization action
     */
    public function log_action($action, $description = '', $status = 'success') {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_logs';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'action' => $action,
                'description' => $description,
                'status' => $status,
                'log_date' => current_time('mysql'),
                'user_id' => get_current_user_id()
            ),
            array(
                '%s', '%s', '%s', '%s', '%d'
            )
        );
        
        return $result !== false;
    }
    
    /**
     * Get optimization logs
     */
    public function get_logs($limit = 50) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'speed_optimizer_logs';
        
        $sql = $wpdb->prepare(
            "SELECT l.*, u.display_name 
             FROM $table_name l 
             LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
             ORDER BY log_date DESC 
             LIMIT %d",
            $limit
        );
        
        return $wpdb->get_results($sql);
    }
    
    /**
     * Clean old data
     */
    public function cleanup_old_data($days = 30) {
        global $wpdb;
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-$days days"));
        
        // Clean old test results
        $tests_table = $wpdb->prefix . 'speed_optimizer_tests';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $tests_table WHERE test_date < %s",
            $date_threshold
        ));
        
        // Clean old logs
        $logs_table = $wpdb->prefix . 'speed_optimizer_logs';
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $logs_table WHERE log_date < %s",
            $date_threshold
        ));
    }
    
    /**
     * Get statistics
     */
    public function get_statistics() {
        global $wpdb;
        
        $tests_table = $wpdb->prefix . 'speed_optimizer_tests';
        $logs_table = $wpdb->prefix . 'speed_optimizer_logs';
        
        $stats = array();
        
        // Total tests
        $stats['total_tests'] = $wpdb->get_var("SELECT COUNT(*) FROM $tests_table");
        
        // Average scores
        $stats['avg_desktop_score'] = $wpdb->get_var("SELECT AVG(desktop_score) FROM $tests_table WHERE desktop_score IS NOT NULL");
        $stats['avg_mobile_score'] = $wpdb->get_var("SELECT AVG(mobile_score) FROM $tests_table WHERE mobile_score IS NOT NULL");
        
        // Recent activity
        $stats['recent_tests'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $tests_table WHERE test_date >= %s",
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ));
        
        $stats['recent_optimizations'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $logs_table WHERE log_date >= %s AND status = 'success'",
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ));
        
        return $stats;
    }
}