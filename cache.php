<?php
/**
 * Retrieves the path to the cache folder, creating it if necessary.
 *
 * This function checks if object caching is enabled via `wp_cache_get`.
 * If enabled, it attempts to retrieve the cache directory path from the cache.
 * If the cache directory path is not found, it defaults to `ABSPATH . 'wp-content/cache/'`.
 * It ensures the cache directory exists, creating it with appropriate permissions if necessary.
 *
 * @return string|false The cache directory path with a trailing slash, or false if object caching is not enabled.
 */
function get_cache_folder_path() {
    // Check if object cache is enabled
    if (function_exists('wp_cache_get')) {
        /**
         * @var string|false $cache_directory The path to the cache directory, or false if not set in the cache.
         */
        $cache_directory = wp_cache_get('cache_directory', 'general');

        // If the cache directory is not set, use the default location
        if (!$cache_directory) {
            /**
             * @var string $cache_directory The default cache directory path if not set in the cache.
             */
            $cache_directory = ABSPATH . 'wp-content/cache/';
        }

        // Ensure that the cache directory exists, and create it if necessary
        if (!is_dir($cache_directory)) {
            /**
             * Create the cache directory with the appropriate permissions (e.g., 0755).
             * The `true` argument ensures that it will create parent directories if necessary.
             *
             * @see mkdir()
             */
            mkdir($cache_directory, 0755, true);
        }

        /**
         * @var string $cache_directory The cache directory path with trailing slash.
         */
        return trailingslashit($cache_directory);
    } else {
        /**
        * Object cache is not enabled. Return false
        */
        return false;
    }
}