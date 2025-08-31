<?php
/**
 * Cache Management for NativeMind Plugin
 * 
 * Provides enhanced caching functionality for translations and content
 * to improve performance and reduce API calls.
 * 
 * @package NativeLang
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Don't access directly.
}

/**
 * Class NativeMindCache
 * 
 * Handles all caching operations for the NativeMind plugin
 */
class NativeMindCache {
    
    /**
     * Cache directory name
     */
    const CACHE_DIR = 'nativemind-cache';
    
    /**
     * Cache expiration time (in seconds)
     */
    const CACHE_EXPIRATION = 7 * 24 * 60 * 60; // 7 days
    
    /**
     * Get cache directory path
     * 
     * @return string Cache directory path
     */
    public static function get_cache_dir() {
        $upload_dir = wp_upload_dir();
        $cache_dir = $upload_dir['basedir'] . '/' . self::CACHE_DIR . '/';
        
        if (!is_dir($cache_dir)) {
            wp_mkdir_p($cache_dir);
            
            // Create .htaccess to protect cache files
            $htaccess_content = "Order deny,allow\nDeny from all\n";
            file_put_contents($cache_dir . '.htaccess', $htaccess_content);
            
            // Create index.php to prevent directory listing
            file_put_contents($cache_dir . 'index.php', '<?php // Silence is golden');
        }
        
        return $cache_dir;
    }
    
    /**
     * Generate cache key for content
     * 
     * @param string $content Content to cache
     * @param string $lang_from Source language
     * @param string $lang_to Target language
     * @param string $type Cache type (translation, post, etc.)
     * @return string Cache key
     */
    public static function generate_cache_key($content, $lang_from, $lang_to, $type = 'translation') {
        $blog_id = get_current_blog_id();
        $network_id = get_current_network_id();
        
        return md5($type . '_' . $blog_id . '_' . $network_id . '_' . $content . '_' . $lang_from . '_' . $lang_to);
    }
    
    /**
     * Get cached content
     * 
     * @param string $cache_key Cache key
     * @return string|false Cached content or false if not found/expired
     */
    public static function get($cache_key) {
        $cache_dir = self::get_cache_dir();
        $cache_file = $cache_dir . $cache_key . '.cache';
        
        if (!file_exists($cache_file)) {
            return false;
        }
        
        // Check if cache has expired
        $file_time = filemtime($cache_file);
        if ((time() - $file_time) > self::CACHE_EXPIRATION) {
            unlink($cache_file);
            return false;
        }
        
        $cached_data = file_get_contents($cache_file);
        return $cached_data !== false ? $cached_data : false;
    }
    
    /**
     * Set cached content
     * 
     * @param string $cache_key Cache key
     * @param string $content Content to cache
     * @return bool True on success, false on failure
     */
    public static function set($cache_key, $content) {
        $cache_dir = self::get_cache_dir();
        $cache_file = $cache_dir . $cache_key . '.cache';
        
        return file_put_contents($cache_file, $content) !== false;
    }
    
    /**
     * Delete specific cache entry
     * 
     * @param string $cache_key Cache key
     * @return bool True on success, false on failure
     */
    public static function delete($cache_key) {
        $cache_dir = self::get_cache_dir();
        $cache_file = $cache_dir . $cache_key . '.cache';
        
        if (file_exists($cache_file)) {
            return unlink($cache_file);
        }
        
        return true;
    }
    
    /**
     * Clear all cache files
     * 
     * @return int Number of files cleared
     */
    public static function clear_all() {
        $cache_dir = self::get_cache_dir();
        $files = glob($cache_dir . '*.cache');
        $cleared = 0;
        
        foreach ($files as $file) {
            if (unlink($file)) {
                $cleared++;
            }
        }
        
        return $cleared;
    }
    
    /**
     * Clean expired cache files
     * 
     * @return int Number of files cleaned
     */
    public static function clean_expired() {
        $cache_dir = self::get_cache_dir();
        $files = glob($cache_dir . '*.cache');
        $cleaned = 0;
        
        foreach ($files as $file) {
            $file_time = filemtime($file);
            if ((time() - $file_time) > self::CACHE_EXPIRATION) {
                if (unlink($file)) {
                    $cleaned++;
                }
            }
        }
        
        return $cleaned;
    }
    
    /**
     * Get cache statistics
     * 
     * @return array Cache statistics
     */
    public static function get_stats() {
        $cache_dir = self::get_cache_dir();
        $files = glob($cache_dir . '*.cache');
        $total_files = count($files);
        $total_size = 0;
        $expired_files = 0;
        
        foreach ($files as $file) {
            $total_size += filesize($file);
            $file_time = filemtime($file);
            if ((time() - $file_time) > self::CACHE_EXPIRATION) {
                $expired_files++;
            }
        }
        
        return array(
            'total_files' => $total_files,
            'total_size' => $total_size,
            'total_size_formatted' => size_format($total_size),
            'expired_files' => $expired_files,
            'cache_dir' => $cache_dir
        );
    }
}

/**
 * Legacy function for backward compatibility
 * 
 * Retrieves the path to the cache folder, creating it if necessary.
 * 
 * @deprecated Use NativeMindCache::get_cache_dir() instead
 * @return string The cache directory path with a trailing slash
 */
function get_cache_folder_path() {
    return NativeMindCache::get_cache_dir();
}

/**
 * Enhanced cache functions for better performance
 */

/**
 * Get translation from cache
 * 
 * @param int $post_id Post ID
 * @param string $language Language code
 * @param string $type Content type (post, title, etc.)
 * @return string|false Cached translation or false
 */
function nm_get_cached_translation($post_id, $language, $type = 'post') {
    $cache_key = NativeMindCache::generate_cache_key($post_id, 'default', $language, $type);
    return NativeMindCache::get($cache_key);
}

/**
 * Set translation in cache
 * 
 * @param int $post_id Post ID
 * @param string $language Language code
 * @param string $content Translated content
 * @param string $type Content type (post, title, etc.)
 * @return bool True on success
 */
function nm_set_cached_translation($post_id, $language, $content, $type = 'post') {
    $cache_key = NativeMindCache::generate_cache_key($post_id, 'default', $language, $type);
    return NativeMindCache::set($cache_key, $content);
}

/**
 * Schedule cache cleanup
 */
function nm_schedule_cache_cleanup() {
    if (!wp_next_scheduled('nm_cache_cleanup')) {
        wp_schedule_event(time(), 'daily', 'nm_cache_cleanup');
    }
}

/**
 * Cleanup expired cache files
 */
function nm_cleanup_cache() {
    NativeMindCache::clean_expired();
}

// Hook cache cleanup
add_action('nm_cache_cleanup', 'nm_cleanup_cache');

// Schedule cleanup on plugin load
add_action('plugins_loaded', 'nm_schedule_cache_cleanup');