<?php
/**
 * Internationalization (i18n) Configuration for NativeLang WordPress Plugin
 * 
 * This file loads and configures all translation arrays and language settings
 * for the NativeLang plugin. It includes menu translations, language configurations,
 * and emoji mappings for enhanced multilingual support.
 * 
 * @package NativeLang
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Don't access directly.
}

// Load menu translation files
require_once plugin_dir_path(__FILE__) . 'i18n/menu/i18n.php';
require_once plugin_dir_path(__FILE__) . 'i18n/menu/ru.php';
require_once plugin_dir_path(__FILE__) . 'i18n/menu/th.php';
require_once plugin_dir_path(__FILE__) . 'i18n/menu/emoji.php';

/**
 * Initialize global translation variables
 * These variables are used throughout the plugin for translations
 */
global $nm_languages, $nm_i18n, $menu_emoji;

// Ensure variables are properly initialized
if (!isset($nm_languages)) {
    $nm_languages = array();
}

if (!isset($nm_i18n)) {
    $nm_i18n = array();
}

if (!isset($menu_emoji)) {
    $menu_emoji = array();
}

/**
 * Get the current language code for translation purposes
 * 
 * @return string Current language code or default language
 */
function nm_get_current_language() {
    if (function_exists('pll_current_language')) {
        return pll_current_language() ?: pll_default_language();
    }
    return 'en'; // Fallback to English
}

/**
 * Get the default language code
 * 
 * @return string Default language code
 */
function nm_get_default_language() {
    if (function_exists('pll_default_language')) {
        return pll_default_language();
    }
    return 'en'; // Fallback to English
}

/**
 * Check if Polylang is active and properly configured
 * 
 * @return bool True if Polylang is available and configured
 */
function nm_is_polylang_active() {
    return function_exists('pll_current_language') && 
           function_exists('pll_default_language') && 
           function_exists('pll_get_post') && 
           function_exists('pll_the_languages');
}

/**
 * Get available languages from Polylang
 * 
 * @return array Array of available languages
 */
function nm_get_available_languages() {
    if (function_exists('pll_the_languages')) {
        return pll_the_languages(array('raw' => 1));
    }
    return array();
}

/**
 * Translate a menu item using predefined translations
 * 
 * @param string $text Text to translate
 * @param string $language Target language code
 * @return string Translated text or original text if no translation found
 */
function nm_translate_menu_item($text, $language = null) {
    global $nm_i18n;
    
    if ($language === null) {
        $language = nm_get_current_language();
    }
    
    if (isset($nm_i18n[$language][$text])) {
        return $nm_i18n[$language][$text];
    }
    
    return $text;
}

/**
 * Get emoji for a menu item
 * 
 * @param string $text Menu item text
 * @return string Emoji or empty string if not found
 */
function nm_get_menu_emoji($text) {
    global $menu_emoji;
    
    if (isset($menu_emoji[$text])) {
        return $menu_emoji[$text];
    }
    
    return '';
}

/**
 * Load language-specific translation file
 * 
 * @param string $language Language code
 * @return bool True if file was loaded successfully
 */
function nm_load_language_file($language) {
    $file_path = plugin_dir_path(__FILE__) . "i18n/menu/{$language}.php";
    
    if (file_exists($file_path)) {
        require_once $file_path;
        return true;
    }
    
    return false;
}

/**
 * Initialize all language files and configurations
 */
function nm_init_i18n() {
    // Load all available language files
    $language_files = glob(plugin_dir_path(__FILE__) . 'i18n/menu/*.php');
    
    foreach ($language_files as $file) {
        require_once $file;
    }
    
    // Hook into WordPress init to ensure everything is loaded
    add_action('init', 'nm_setup_language_hooks');
}

/**
 * Setup language-related hooks and filters
 */
function nm_setup_language_hooks() {
    // Add language detection hooks
    add_action('wp_head', 'nm_add_language_meta');
    add_filter('language_attributes', 'nm_add_language_attributes');
}

/**
 * Add language meta tags to head
 */
function nm_add_language_meta() {
    $current_lang = nm_get_current_language();
    echo '<meta name="language" content="' . esc_attr($current_lang) . '">' . "\n";
    
    // Add alternate language links if available
    if (nm_is_polylang_active()) {
        $languages = nm_get_available_languages();
        foreach ($languages as $lang) {
            if ($lang['slug'] !== $current_lang) {
                echo '<link rel="alternate" hreflang="' . esc_attr($lang['slug']) . '" href="' . esc_url($lang['url']) . '">' . "\n";
            }
        }
    }
}

/**
 * Add language attributes to HTML tag
 * 
 * @param string $output Current language attributes
 * @return string Modified language attributes
 */
function nm_add_language_attributes($output) {
    $current_lang = nm_get_current_language();
    
    // Add dir attribute for RTL languages
    $rtl_languages = array('ar', 'he', 'fa', 'ur');
    if (in_array($current_lang, $rtl_languages)) {
        $output .= ' dir="rtl"';
    }
    
    return $output;
}

// Initialize the i18n system
nm_init_i18n();
