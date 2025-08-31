/**
 * NativeMind Admin JavaScript
 * 
 * Handles admin interface interactions for cache management,
 * translation monitoring, and plugin configuration.
 * 
 * @package NativeLang
 * @version 1.0.0
 */

(function($) {
    'use strict';
    
    // Wait for DOM ready
    $(document).ready(function() {
        initNativeMindAdmin();
    });
    
    /**
     * Initialize admin functionality
     */
    function initNativeMindAdmin() {
        initCacheManagement();
        initTranslationMonitor();
        initLanguageHelpers();
    }
    
    /**
     * Initialize cache management functionality
     */
    function initCacheManagement() {
        $('#nm-clear-cache').on('click', function(e) {
            e.preventDefault();
            clearTranslationCache();
        });
        
        // Auto-refresh cache stats every 30 seconds
        setInterval(function() {
            refreshCacheStats();
        }, 30000);
    }
    
    /**
     * Clear translation cache via AJAX
     */
    function clearTranslationCache() {
        const $button = $('#nm-clear-cache');
        const $status = $('#nm-cache-status');
        
        // Disable button and show loading state
        $button.prop('disabled', true).text(nmAjax.messages.clearing);
        $status.html('<span style="color: #666;">⟳ ' + nmAjax.messages.clearing + '</span>');
        
        $.ajax({
            url: nmAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'nm_clear_cache',
                nonce: nmAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
                    
                    // Refresh cache stats after clearing
                    setTimeout(refreshCacheStats, 1000);
                } else {
                    $status.html('<span style="color: #dc3232;">✗ ' + (response.data.message || nmAjax.messages.error) + '</span>');
                }
            },
            error: function() {
                $status.html('<span style="color: #dc3232;">✗ ' + nmAjax.messages.error + '</span>');
            },
            complete: function() {
                // Re-enable button
                $button.prop('disabled', false).text($button.data('original-text') || 'Clear Translation Cache');
                
                // Clear status after 5 seconds
                setTimeout(function() {
                    $status.html('');
                }, 5000);
            }
        });
    }
    
    /**
     * Refresh cache statistics
     */
    function refreshCacheStats() {
        $.ajax({
            url: nmAjax.ajaxUrl,
            type: 'POST',
            data: {
                action: 'nm_get_cache_stats',
                nonce: nmAjax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    updateCacheStatsDisplay(response.data);
                }
            }
        });
    }
    
    /**
     * Update cache stats in the UI
     */
    function updateCacheStatsDisplay(stats) {
        const $statsContainer = $('#nm-cache-stats');
        
        if ($statsContainer.length === 0) {
            // Create stats container if it doesn't exist
            $('.card h2').filter(function() {
                return $(this).text().indexOf('Translation Cache') !== -1;
            }).after('<div id="nm-cache-stats"></div>');
        }
        
        const statsHTML = `
            <table class="widefat" style="margin-top: 10px;">
                <tr>
                    <td><strong>Total Cache Files:</strong></td>
                    <td>${stats.total_files}</td>
                </tr>
                <tr>
                    <td><strong>Cache Size:</strong></td>
                    <td>${stats.total_size_formatted}</td>
                </tr>
                <tr>
                    <td><strong>Expired Files:</strong></td>
                    <td>${stats.expired_files}</td>
                </tr>
            </table>
        `;
        
        $('#nm-cache-stats').html(statsHTML);
    }
    
    /**
     * Initialize translation monitoring
     */
    function initTranslationMonitor() {
        // Monitor for translation status updates
        $(document).on('nm_translation_start', function(e, data) {
            showTranslationStatus('Translating content...', 'info');
        });
        
        $(document).on('nm_translation_complete', function(e, data) {
            showTranslationStatus('Translation completed', 'success');
        });
        
        $(document).on('nm_translation_error', function(e, data) {
            showTranslationStatus('Translation failed: ' + data.message, 'error');
        });
    }
    
    /**
     * Show translation status message
     */
    function showTranslationStatus(message, type) {
        const $statusContainer = $('#nm-translation-status');
        
        if ($statusContainer.length === 0) {
            $('body').append('<div id="nm-translation-status"></div>');
        }
        
        const typeClass = {
            'info': 'notice-info',
            'success': 'notice-success', 
            'error': 'notice-error'
        }[type] || 'notice-info';
        
        const statusHTML = `
            <div class="notice ${typeClass} is-dismissible" style="position: fixed; top: 32px; right: 20px; z-index: 9999; max-width: 300px;">
                <p>${message}</p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss</span></button>
            </div>
        `;
        
        const $notice = $(statusHTML);
        $('body').append($notice);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        }, 5000);
        
        // Handle dismiss button
        $notice.find('.notice-dismiss').on('click', function() {
            $notice.fadeOut(function() {
                $(this).remove();
            });
        });
    }
    
    /**
     * Initialize language helpers
     */
    function initLanguageHelpers() {
        // Add language flags to Polylang language list if present
        $('.language-item').each(function() {
            const $item = $(this);
            const langCode = $item.data('language');
            
            if (langCode && nmAjax.languageFlags && nmAjax.languageFlags[langCode]) {
                $item.prepend('<span class="language-flag">' + nmAjax.languageFlags[langCode] + '</span> ');
            }
        });
        
        // Add tooltips to emoji menu items
        $('.menu-item-emoji').each(function() {
            const $emoji = $(this);
            const emojiChar = $emoji.text().trim();
            
            if (emojiChar && nmAjax.emojiTooltips && nmAjax.emojiTooltips[emojiChar]) {
                $emoji.attr('title', nmAjax.emojiTooltips[emojiChar]);
            }
        });
    }
    
    /**
     * Utility function to format bytes
     */
    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }
    
    /**
     * Export functions for external use
     */
    window.NativeMindAdmin = {
        clearCache: clearTranslationCache,
        refreshStats: refreshCacheStats,
        showStatus: showTranslationStatus
    };
    
})(jQuery);
