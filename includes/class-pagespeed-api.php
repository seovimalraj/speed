<?php
/**
 * PageSpeed Insights API integration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Speed_Optimizer_PageSpeed_API {
    
    private $api_key;
    private $api_url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
    
    public function __construct() {
        $this->api_key = get_option('speed_optimizer_pagespeed_api_key', '');
    }
    
    /**
     * Test URL with PageSpeed Insights
     */
    public function test_url($url, $strategy = 'both') {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'error' => __('PageSpeed Insights API key is required', 'speed-optimizer')
            );
        }
        
        $results = array(
            'success' => true,
            'url' => $url,
            'desktop' => null,
            'mobile' => null,
            'timestamp' => current_time('mysql')
        );
        
        // Test desktop
        if ($strategy === 'both' || $strategy === 'desktop') {
            $desktop_result = $this->run_test($url, 'desktop');
            $results['desktop'] = $desktop_result;
        }
        
        // Test mobile
        if ($strategy === 'both' || $strategy === 'mobile') {
            $mobile_result = $this->run_test($url, 'mobile');
            $results['mobile'] = $mobile_result;
        }
        
        // Save to database
        if ($results['desktop'] && $results['mobile']) {
            $database = new Speed_Optimizer_Database();
            $database->save_speed_test(
                $url,
                $results['desktop'],
                $results['mobile'],
                json_encode($results)
            );
        }
        
        return $results;
    }
    
    /**
     * Run single PageSpeed test
     */
    private function run_test($url, $strategy = 'mobile') {
        $api_url = add_query_arg(array(
            'url' => urlencode($url),
            'key' => $this->api_key,
            'strategy' => $strategy,
            'category' => 'performance',
            'locale' => get_locale()
        ), $this->api_url);
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 60,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            return array(
                'error' => $response->get_error_message(),
                'score' => 0
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!isset($data['lighthouseResult'])) {
            return array(
                'error' => __('Invalid response from PageSpeed Insights', 'speed-optimizer'),
                'score' => 0
            );
        }
        
        return $this->parse_result($data);
    }
    
    /**
     * Parse PageSpeed Insights result
     */
    private function parse_result($data) {
        $lighthouse = $data['lighthouseResult'];
        $categories = $lighthouse['categories'];
        $audits = $lighthouse['audits'];
        
        $result = array(
            'score' => round($categories['performance']['score'] * 100),
            'metrics' => array(),
            'opportunities' => array(),
            'diagnostics' => array()
        );
        
        // Core Web Vitals
        if (isset($audits['first-contentful-paint'])) {
            $result['metrics']['fcp'] = $audits['first-contentful-paint']['displayValue'];
            $result['fcp'] = $this->extract_numeric_value($audits['first-contentful-paint']['displayValue']);
        }
        
        if (isset($audits['largest-contentful-paint'])) {
            $result['metrics']['lcp'] = $audits['largest-contentful-paint']['displayValue'];
            $result['lcp'] = $this->extract_numeric_value($audits['largest-contentful-paint']['displayValue']);
        }
        
        if (isset($audits['cumulative-layout-shift'])) {
            $result['metrics']['cls'] = $audits['cumulative-layout-shift']['displayValue'];
        }
        
        if (isset($audits['speed-index'])) {
            $result['metrics']['si'] = $audits['speed-index']['displayValue'];
        }
        
        if (isset($audits['total-blocking-time'])) {
            $result['metrics']['tbt'] = $audits['total-blocking-time']['displayValue'];
        }
        
        // Opportunities (things to fix)
        $opportunity_keys = array(
            'render-blocking-resources',
            'unused-css-rules',
            'unused-javascript',
            'modern-image-formats',
            'offscreen-images',
            'minify-css',
            'minify-javascript',
            'efficient-animated-content',
            'duplicated-javascript'
        );
        
        foreach ($opportunity_keys as $key) {
            if (isset($audits[$key]) && isset($audits[$key]['details'])) {
                $result['opportunities'][] = array(
                    'id' => $key,
                    'title' => $audits[$key]['title'],
                    'description' => $audits[$key]['description'],
                    'score' => $audits[$key]['score'],
                    'displayValue' => isset($audits[$key]['displayValue']) ? $audits[$key]['displayValue'] : ''
                );
            }
        }
        
        // Diagnostics
        $diagnostic_keys = array(
            'mainthread-work-breakdown',
            'bootup-time',
            'uses-long-cache-ttl',
            'total-byte-weight',
            'dom-size'
        );
        
        foreach ($diagnostic_keys as $key) {
            if (isset($audits[$key])) {
                $result['diagnostics'][] = array(
                    'id' => $key,
                    'title' => $audits[$key]['title'],
                    'description' => $audits[$key]['description'],
                    'score' => $audits[$key]['score'],
                    'displayValue' => isset($audits[$key]['displayValue']) ? $audits[$key]['displayValue'] : ''
                );
            }
        }
        
        return $result;
    }
    
    /**
     * Extract numeric value from display string
     */
    private function extract_numeric_value($display_value) {
        preg_match('/[\d.]+/', $display_value, $matches);
        return isset($matches[0]) ? floatval($matches[0]) : 0;
    }
    
    /**
     * Get score color class
     */
    public static function get_score_color($score) {
        if ($score >= 90) {
            return 'good';
        } elseif ($score >= 50) {
            return 'average';
        } else {
            return 'poor';
        }
    }
    
    /**
     * Get score text
     */
    public static function get_score_text($score) {
        if ($score >= 90) {
            return __('Good', 'speed-optimizer');
        } elseif ($score >= 50) {
            return __('Needs Improvement', 'speed-optimizer');
        } else {
            return __('Poor', 'speed-optimizer');
        }
    }
    
    /**
     * Validate API key
     */
    public function validate_api_key($api_key) {
        $test_url = home_url();
        $api_url = add_query_arg(array(
            'url' => urlencode($test_url),
            'key' => $api_key,
            'strategy' => 'mobile'
        ), $this->api_url);
        
        $response = wp_remote_get($api_url, array(
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        return $response_code === 200;
    }
}