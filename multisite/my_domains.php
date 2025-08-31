<?php
/**
 * Domain mapping configuration for NativeMind Multisite
 * 
 * Configure your additional domains here for different language sites.
 * This file is automatically loaded by sunrise.php
 * 
 * @package NativeLang
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Don't access directly.
}

// Example domain mappings - customize these for your setup

// Main site (English) - Blog ID 1
cybo_add_extra_domain( 'taxlien.online', '/', 1 );
cybo_add_extra_domain( 'www.taxlien.online', '/', 1 );

// Language-specific subdomains
cybo_add_extra_domain( 'en.taxlien.online', '/', 1 );  // English site
cybo_add_extra_domain( 'ru.taxlien.online', '/', 2 );  // Russian site
cybo_add_extra_domain( 'th.taxlien.online', '/', 3 );  // Thai site
cybo_add_extra_domain( 'zh.taxlien.online', '/', 4 );  // Chinese site
cybo_add_extra_domain( 'hi.taxlien.online', '/', 5 );  // Hindi site

// Alternative domain patterns
cybo_add_extra_domain( 'taxlien.ru', '/', 2 );         // Russian domain
cybo_add_extra_domain( 'taxlien.th', '/', 3 );         // Thai domain  
cybo_add_extra_domain( 'taxlien.cn', '/', 4 );         // Chinese domain

// Development/staging domains
if (defined('WP_DEBUG') && WP_DEBUG) {
    cybo_add_extra_domain( 'dev.taxlien.online', '/', 1 );
    cybo_add_extra_domain( 'staging.taxlien.online', '/', 1 );
}

/**
 * Language-to-blog mapping
 * Maps language codes to blog IDs for automatic language detection
 */
global $nm_multisite_language_map;
$nm_multisite_language_map = array(
    'en' => 1,  // English - main site
    'ru' => 2,  // Russian site
    'th' => 3,  // Thai site  
    'zh' => 4,  // Chinese site
    'hi' => 5,  // Hindi site
);

/**
 * Domain-to-language mapping
 * Maps domains to language codes for automatic language detection
 */
global $nm_multisite_domain_map;
$nm_multisite_domain_map = array(
    'taxlien.online' => 'en',
    'www.taxlien.online' => 'en',
    'en.taxlien.online' => 'en',
    'ru.taxlien.online' => 'ru',
    'th.taxlien.online' => 'th',
    'zh.taxlien.online' => 'zh',
    'hi.taxlien.online' => 'hi',
    'taxlien.ru' => 'ru',
    'taxlien.th' => 'th',
    'taxlien.cn' => 'zh',
);

/**
 * Get blog ID for a given language
 * 
 * @param string $language Language code
 * @return int Blog ID or 1 (main site) if not found
 */
function nm_get_blog_id_for_language($language) {
    global $nm_multisite_language_map;
    return isset($nm_multisite_language_map[$language]) ? $nm_multisite_language_map[$language] : 1;
}

/**
 * Get language for current domain
 * 
 * @return string Language code or 'en' if not found
 */
function nm_get_language_for_domain() {
    global $nm_multisite_domain_map;
    
    if (!isset($_SERVER['HTTP_HOST'])) {
        return 'en';
    }
    
    $domain = strtolower($_SERVER['HTTP_HOST']);
    return isset($nm_multisite_domain_map[$domain]) ? $nm_multisite_domain_map[$domain] : 'en';
}

/**
 * Get URL for a specific language
 * 
 * @param string $language Language code
 * @param string $path Optional path to append
 * @return string URL for the language
 */
function nm_get_language_url($language, $path = '/') {
    global $nm_multisite_language_map, $nm_multisite_domain_map;
    
    // Find domain for this language
    $target_domain = 'taxlien.online'; // Default
    
    foreach ($nm_multisite_domain_map as $domain => $lang) {
        if ($lang === $language) {
            $target_domain = $domain;
            break;
        }
    }
    
    $protocol = is_ssl() ? 'https://' : 'http://';
    return $protocol . $target_domain . $path;
}
