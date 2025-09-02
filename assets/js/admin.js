/**
 * Speed Optimizer Admin JavaScript
 */

(function($) {
    'use strict';
    
    var SpeedOptimizer = {
        
        init: function() {
            this.bindEvents();
            this.initTabs();
        },
        
        bindEvents: function() {
            // Speed test form
            $('#quick-speed-test, #speed-test-form').on('submit', this.runSpeedTest);
            
            // Quick actions
            $('#clear-cache, #clear-all-cache').on('click', this.clearCache);
            $('#optimize-database').on('click', this.optimizeDatabase);
            
            // API key test
            $('#test-api-key').on('click', this.testApiKey);
            
            // Settings import/export
            $('#export-settings').on('click', this.exportSettings);
            $('#import-settings').on('click', this.importSettings);
            
            // View test details
            $('.view-details').on('click', this.viewTestDetails);
            
            // Modal close
            $('.modal-close, .modal-overlay').on('click', this.closeModal);
        },
        
        initTabs: function() {
            $('.nav-tab').on('click', function(e) {
                e.preventDefault();
                
                var target = $(this).attr('href');
                
                // Update tab appearance
                $('.nav-tab').removeClass('nav-tab-active');
                $(this).addClass('nav-tab-active');
                
                // Show target content
                $('.tab-content').hide();
                $(target).show();
            });
        },
        
        runSpeedTest: function(e) {
            e.preventDefault();
            
            var form = $(this);
            var url = form.find('#test-url').val();
            var strategy = form.find('#test-strategy').val() || 'both';
            
            if (!url) {
                alert(speedOptimizer.strings.error + ': URL is required');
                return;
            }
            
            // Show progress
            $('#test-progress').show();
            $('#test-results').hide();
            form.find('button[type="submit"]').prop('disabled', true).addClass('loading');
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                timeout: 180000, // 3 minutes timeout to handle slow PageSpeed tests
                data: {
                    action: 'speed_optimizer_test',
                    url: url,
                    strategy: strategy,
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    SpeedOptimizer.displayTestResults(response);
                },
                error: function(xhr, status, error) {
                    var errorMessage = speedOptimizer.strings.error;
                    
                    if (status === 'timeout') {
                        errorMessage = 'The PageSpeed test took too long to complete. This can happen with very slow websites or when Google\'s servers are busy. Please try again later.';
                    } else if (xhr.responseJSON && xhr.responseJSON.data) {
                        errorMessage = xhr.responseJSON.data;
                    }
                    
                    alert(errorMessage);
                },
                complete: function() {
                    $('#test-progress').hide();
                    form.find('button[type="submit"]').prop('disabled', false).removeClass('loading');
                }
            });
        },
        
        displayTestResults: function(data) {
            if (!data.success) {
                alert(speedOptimizer.strings.error + ': ' + (data.error || 'Unknown error'));
                return;
            }
            
            var html = '<div class="test-result-grid">';
            var hasResults = false;
            
            // Desktop results
            if (data.desktop) {
                if (data.desktop.error) {
                    html += '<div class="result-card error">';
                    html += '<h4>Desktop</h4>';
                    html += '<div class="error-message">Error: ' + data.desktop.error + '</div>';
                    html += '</div>';
                } else {
                    hasResults = true;
                    html += '<div class="result-card">';
                    html += '<h4>Desktop</h4>';
                    html += '<div class="score-display score-' + SpeedOptimizer.getScoreColor(data.desktop.score) + '">' + data.desktop.score + '</div>';
                    
                    if (data.desktop.metrics) {
                        html += '<ul class="metrics-list">';
                        for (var metric in data.desktop.metrics) {
                            html += '<li><span>' + metric.toUpperCase() + '</span><span>' + data.desktop.metrics[metric] + '</span></li>';
                        }
                        html += '</ul>';
                    }
                    
                    html += '</div>';
                }
            }
            
            // Mobile results
            if (data.mobile) {
                if (data.mobile.error) {
                    html += '<div class="result-card error">';
                    html += '<h4>Mobile</h4>';
                    html += '<div class="error-message">Error: ' + data.mobile.error + '</div>';
                    html += '</div>';
                } else {
                    hasResults = true;
                    html += '<div class="result-card">';
                    html += '<h4>Mobile</h4>';
                    html += '<div class="score-display score-' + SpeedOptimizer.getScoreColor(data.mobile.score) + '">' + data.mobile.score + '</div>';
                
                if (data.mobile.metrics) {
                    html += '<ul class="metrics-list">';
                    for (var metric in data.mobile.metrics) {
                        html += '<li><span>' + metric.toUpperCase() + '</span><span>' + data.mobile.metrics[metric] + '</span></li>';
                    }
                    html += '</ul>';
                }
                
                html += '</div>';
                }
            }
            
            html += '</div>';
            
            // Show message if no results were obtained
            if (!hasResults) {
                html += '<div class="no-results-message">';
                html += '<p>Unable to complete the PageSpeed test due to timeout issues. Please try again later or check if the website URL is accessible.</p>';
                html += '</div>';
            }
            
            // Opportunities (only if we have mobile results without errors)
            if (data.mobile && !data.mobile.error && data.mobile.opportunities && data.mobile.opportunities.length > 0) {
                html += '<div class="opportunities-section">';
                html += '<h4>Optimization Opportunities</h4>';
                html += '<div class="opportunities-list">';
                
                data.mobile.opportunities.forEach(function(opportunity) {
                    html += '<div class="opportunity-item">';
                    html += '<h5>' + opportunity.title + '</h5>';
                    html += '<p>' + opportunity.description + '</p>';
                    if (opportunity.displayValue) {
                        html += '<small>Potential savings: ' + opportunity.displayValue + '</small>';
                    }
                    html += '</div>';
                });
                
                html += '</div>';
                html += '</div>';
            }
            
            $('#results-content').html(html);
            $('#test-results').show();
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $('#test-results').offset().top - 50
            }, 500);
        },
        
        getScoreColor: function(score) {
            if (score >= 90) return 'good';
            if (score >= 50) return 'average';
            return 'poor';
        },
        
        clearCache: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to clear all cache?')) {
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true).addClass('loading');
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'speed_optimizer_clear_cache',
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SpeedOptimizer.showNotice('Cache cleared successfully!', 'success');
                    } else {
                        SpeedOptimizer.showNotice('Failed to clear cache.', 'error');
                    }
                },
                error: function() {
                    SpeedOptimizer.showNotice('Failed to clear cache.', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).removeClass('loading');
                }
            });
        },
        
        optimizeDatabase: function(e) {
            e.preventDefault();
            
            if (!confirm('Are you sure you want to optimize the database? This may take a few minutes.')) {
                return;
            }
            
            var button = $(this);
            button.prop('disabled', true).addClass('loading');
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'run_database_optimization',
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        var message = 'Database optimized successfully! ';
                        if (response.data) {
                            message += 'Cleaned ' + (response.data.transients || 0) + ' transients, ';
                            message += (response.data.postmeta || 0) + ' orphaned postmeta, ';
                            message += (response.data.spam_comments || 0) + ' spam comments, ';
                            message += 'optimized ' + (response.data.tables || 0) + ' tables.';
                        }
                        SpeedOptimizer.showNotice(message, 'success');
                    } else {
                        SpeedOptimizer.showNotice('Failed to optimize database.', 'error');
                    }
                },
                error: function() {
                    SpeedOptimizer.showNotice('Failed to optimize database.', 'error');
                },
                complete: function() {
                    button.prop('disabled', false).removeClass('loading');
                }
            });
        },
        
        testApiKey: function(e) {
            e.preventDefault();
            
            var apiKey = $('input[name="pagespeed_api_key"]').val();
            var button = $(this);
            var status = $('#api-key-status');
            
            if (!apiKey) {
                status.html('<span style="color: red;">Please enter an API key first.</span>');
                return;
            }
            
            button.prop('disabled', true).addClass('loading');
            status.html('<span class="spinner"></span> Testing...');
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'speed_optimizer_test_api_key',
                    api_key: apiKey,
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        status.html('<span style="color: green;">✓ API key is valid!</span>');
                    } else {
                        status.html('<span style="color: red;">✗ API key is invalid.</span>');
                    }
                },
                error: function() {
                    status.html('<span style="color: red;">✗ Failed to test API key.</span>');
                },
                complete: function() {
                    button.prop('disabled', false).removeClass('loading');
                }
            });
        },
        
        exportSettings: function(e) {
            e.preventDefault();
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'speed_optimizer_export_settings',
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        SpeedOptimizer.downloadFile('speed-optimizer-settings.json', response.data);
                    } else {
                        SpeedOptimizer.showNotice('Failed to export settings.', 'error');
                    }
                },
                error: function() {
                    SpeedOptimizer.showNotice('Failed to export settings.', 'error');
                }
            });
        },
        
        importSettings: function(e) {
            e.preventDefault();
            
            var fileInput = $('#import-file')[0];
            var file = fileInput.files[0];
            
            if (!file) {
                alert('Please select a file to import.');
                return;
            }
            
            if (!confirm('This will overwrite your current settings. Are you sure?')) {
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(e) {
                $.ajax({
                    url: speedOptimizer.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'speed_optimizer_import_settings',
                        settings_data: e.target.result,
                        nonce: speedOptimizer.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            SpeedOptimizer.showNotice('Settings imported successfully! Page will reload.', 'success');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            SpeedOptimizer.showNotice('Failed to import settings.', 'error');
                        }
                    },
                    error: function() {
                        SpeedOptimizer.showNotice('Failed to import settings.', 'error');
                    }
                });
            };
            reader.readAsText(file);
        },
        
        viewTestDetails: function(e) {
            e.preventDefault();
            
            var testId = $(this).data('test-id');
            
            $.ajax({
                url: speedOptimizer.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'speed_optimizer_get_test_details',
                    test_id: testId,
                    nonce: speedOptimizer.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#test-details-content').html(response.data);
                        $('#test-details-modal').show();
                    } else {
                        SpeedOptimizer.showNotice('Failed to load test details.', 'error');
                    }
                },
                error: function() {
                    SpeedOptimizer.showNotice('Failed to load test details.', 'error');
                }
            });
        },
        
        closeModal: function(e) {
            if (e.target === this || $(e.target).hasClass('modal-close')) {
                $('#test-details-modal').hide();
            }
        },
        
        downloadFile: function(filename, content) {
            var element = document.createElement('a');
            element.setAttribute('href', 'data:application/json;charset=utf-8,' + encodeURIComponent(content));
            element.setAttribute('download', filename);
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        },
        
        showNotice: function(message, type) {
            var notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');
            $('.wrap h1').after(notice);
            
            setTimeout(function() {
                notice.fadeOut();
            }, 5000);
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        SpeedOptimizer.init();
    });
    
})(jQuery);