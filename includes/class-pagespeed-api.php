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
    private function run_test($url, $strategy = 'mobile', $retry_count = 0) {
        $max_retries = 2;
        $timeout = 90; // Increased timeout for PageSpeed tests
        
        $api_url = add_query_arg(array(
            'url' => urlencode($url),
            'key' => $this->api_key,
            'strategy' => $strategy,
            'category' => 'performance',
            'locale' => get_locale()
        ), $this->api_url);
        
        $response = wp_remote_get($api_url, array(
            'timeout' => $timeout,
            'headers' => array(
                'Accept' => 'application/json'
            )
        ));
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            // Handle timeout errors with retry logic
            if (strpos($error_message, 'timeout') !== false && $retry_count < $max_retries) {
                // Exponential backoff: wait 2^retry_count seconds
                sleep(pow(2, $retry_count));
                return $this->run_test($url, $strategy, $retry_count + 1);
            }
            
            return array(
                'error' => $this->format_error_message($error_message),
                'score' => 0
            );
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Check for API errors in response
        if (isset($data['error'])) {
            $api_error = $data['error'];
            $error_message = isset($api_error['message']) ? $api_error['message'] : 'Unknown API error';
            
            // Handle DEADLINE_EXCEEDED specifically
            if (strpos($error_message, 'DEADLINE_EXCEEDED') !== false) {
                if ($retry_count < $max_retries) {
                    // Wait longer for deadline exceeded errors
                    sleep(pow(2, $retry_count) + 3);
                    return $this->run_test($url, $strategy, $retry_count + 1);
                }
                
                return array(
                    'error' => __('The PageSpeed test took too long to complete. This usually happens with very slow websites or when Google\'s servers are busy. Please try again later.', 'speed-optimizer'),
                    'score' => 0
                );
            }
            
            return array(
                'error' => $this->format_error_message($error_message),
                'score' => 0
            );
        }
        
        if (!isset($data['lighthouseResult'])) {
            return array(
                'error' => __('Invalid response from PageSpeed Insights', 'speed-optimizer'),
                'score' => 0
            );
        }
        
        return $this->parse_result($data);
    }
    
    /**
     * Format error message for user display
     */
    private function format_error_message($error_message) {
        // Handle common error messages
        if (strpos($error_message, 'DEADLINE_EXCEEDED') !== false) {
            return __('The PageSpeed test took too long to complete. Please try again later.', 'speed-optimizer');
        }
        
        if (strpos($error_message, 'timeout') !== false) {
            return __('The request timed out. Please check your internet connection and try again.', 'speed-optimizer');
        }
        
        if (strpos($error_message, 'INVALID_ARGUMENT') !== false) {
            return __('Invalid URL provided. Please check the URL and try again.', 'speed-optimizer');
        }
        
        if (strpos($error_message, 'QUOTA_EXCEEDED') !== false) {
            return __('API quota exceeded. Please try again later or check your API key limits.', 'speed-optimizer');
        }
        
        // Return original message for other errors
        return $error_message;
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
            'timeout' => 45 // Increased timeout for API key validation
        ));
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Also check for API errors in the response body
        if ($response_code === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            
            // If there's an error in the response, the API key might be invalid
            if (isset($data['error'])) {
                $error_code = isset($data['error']['code']) ? $data['error']['code'] : '';
                
                // Don't fail validation for DEADLINE_EXCEEDED, as that's a server issue, not API key issue
                if ($error_code === 'DEADLINE_EXCEEDED') {
                    return true;
                }
                
                return false;
            }
        }
        
        return $response_code === 200;
    }
}