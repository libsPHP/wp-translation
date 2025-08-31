<?php
/**
 * Test script for NativeMind WordPress Plugin functionality
 * 
 * This script tests various components of the plugin including:
 * - Translation functionality
 * - Cache operations
 * - Language detection
 * - Multisite support
 * 
 * Usage: Include this file in a WordPress environment or run directly with WordPress loaded
 * 
 * @package NativeLang
 * @version 1.0.0
 */

// Ensure WordPress is loaded
if (!defined('ABSPATH')) {
    die('This script requires WordPress to be loaded.');
}

/**
 * Test class for NativeMind functionality
 */
class NativeMindTest {
    
    private $results = array();
    private $total_tests = 0;
    private $passed_tests = 0;
    
    public function __construct() {
        echo "<h1>NativeMind Plugin Test Suite</h1>\n";
        echo "<style>
            .test-pass { color: green; font-weight: bold; }
            .test-fail { color: red; font-weight: bold; }
            .test-warning { color: orange; font-weight: bold; }
            .test-section { margin: 20px 0; padding: 10px; border: 1px solid #ccc; }
        </style>\n";
    }
    
    /**
     * Run all tests
     */
    public function run_all_tests() {
        $this->test_dependencies();
        $this->test_cache_functionality();
        $this->test_translation_functionality();
        $this->test_language_detection();
        $this->test_multisite_support();
        $this->test_admin_functionality();
        
        $this->display_summary();
    }
    
    /**
     * Test plugin dependencies
     */
    private function test_dependencies() {
        echo "<div class='test-section'><h2>Testing Dependencies</h2>\n";
        
        // Test if Polylang functions exist
        $this->assert_true(
            function_exists('pll_current_language'), 
            'Polylang pll_current_language() function exists'
        );
        
        $this->assert_true(
            function_exists('pll_default_language'), 
            'Polylang pll_default_language() function exists'
        );
        
        $this->assert_true(
            function_exists('pll_the_languages'), 
            'Polylang pll_the_languages() function exists'
        );
        
        // Test NativeMind functions
        $this->assert_true(
            function_exists('nm_get_current_language'), 
            'NativeMind nm_get_current_language() function exists'
        );
        
        $this->assert_true(
            function_exists('nm_is_polylang_active'), 
            'NativeMind nm_is_polylang_active() function exists'
        );
        
        $this->assert_true(
            class_exists('NativeMind'), 
            'NativeMind main class exists'
        );
        
        $this->assert_true(
            class_exists('NativeMindCache'), 
            'NativeMindCache class exists'
        );
        
        echo "</div>\n";
    }
    
    /**
     * Test cache functionality
     */
    private function test_cache_functionality() {
        echo "<div class='test-section'><h2>Testing Cache Functionality</h2>\n";
        
        // Test cache directory creation
        $cache_dir = NativeMindCache::get_cache_dir();
        $this->assert_true(
            is_dir($cache_dir), 
            'Cache directory exists: ' . $cache_dir
        );
        
        // Test cache key generation
        $cache_key = NativeMindCache::generate_cache_key('test content', 'en', 'ru', 'test');
        $this->assert_true(
            !empty($cache_key) && is_string($cache_key), 
            'Cache key generation works'
        );
        
        // Test cache set/get operations
        $test_content = 'This is test content for caching';
        $set_result = NativeMindCache::set($cache_key, $test_content);
        $this->assert_true($set_result, 'Cache set operation successful');
        
        $cached_content = NativeMindCache::get($cache_key);
        $this->assert_equals(
            $test_content, 
            $cached_content, 
            'Cache get operation returns correct content'
        );
        
        // Test cache delete
        $delete_result = NativeMindCache::delete($cache_key);
        $this->assert_true($delete_result, 'Cache delete operation successful');
        
        $deleted_content = NativeMindCache::get($cache_key);
        $this->assert_false($deleted_content, 'Deleted cache content not retrievable');
        
        // Test cache statistics
        $stats = NativeMindCache::get_stats();
        $this->assert_true(
            is_array($stats) && isset($stats['total_files']), 
            'Cache statistics function works'
        );
        
        echo "</div>\n";
    }
    
    /**
     * Test translation functionality
     */
    private function test_translation_functionality() {
        echo "<div class='test-section'><h2>Testing Translation Functionality</h2>\n";
        
        // Test Google Translate function existence
        $this->assert_true(
            function_exists('translateTextGoogle_nocache'), 
            'Google Translate function exists'
        );
        
        // Test translation quality checker
        if (function_exists('nm_check_translation_quality')) {
            $quality = nm_check_translation_quality('Hello world', 'Привет мир');
            $this->assert_true(
                is_array($quality) && isset($quality['quality_score']), 
                'Translation quality checker works'
            );
        }
        
        // Test emoji extraction
        if (method_exists('NativeMind', 'get_emoji')) {
            $emoji = (new NativeMind())->get_emoji('🌍 Hello World');
            $this->assert_equals('🌍', $emoji, 'Emoji extraction works');
        }
        
        // Test text cleaning
        if (function_exists('nm_clean_translation_text')) {
            $cleaned = nm_clean_translation_text('  Hello   world  ');
            $this->assert_equals('Hello world', $cleaned, 'Text cleaning works');
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test language detection
     */
    private function test_language_detection() {
        echo "<div class='test-section'><h2>Testing Language Detection</h2>\n";
        
        // Test browser language detection
        if (function_exists('nm_get_browser_language')) {
            $browser_lang = nm_get_browser_language();
            $this->assert_true(
                is_string($browser_lang) && strlen($browser_lang) === 2, 
                'Browser language detection returns valid language code'
            );
        }
        
        // Test flag emoji function
        if (function_exists('nm_get_flag_emoji')) {
            $flag = nm_get_flag_emoji('en');
            $this->assert_true(
                !empty($flag), 
                'Flag emoji function returns emoji for language'
            );
        }
        
        // Test language switcher generation
        if (function_exists('nm_get_language_switcher')) {
            $switcher = nm_get_language_switcher(array('show_flags' => true));
            $this->assert_true(
                is_string($switcher), 
                'Language switcher generation works'
            );
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test multisite support
     */
    private function test_multisite_support() {
        echo "<div class='test-section'><h2>Testing Multisite Support</h2>\n";
        
        // Test domain mapping functions
        if (function_exists('nm_get_language_for_domain')) {
            $domain_lang = nm_get_language_for_domain();
            $this->assert_true(
                is_string($domain_lang), 
                'Domain language detection works'
            );
        }
        
        if (function_exists('nm_get_blog_id_for_language')) {
            $blog_id = nm_get_blog_id_for_language('en');
            $this->assert_true(
                is_numeric($blog_id), 
                'Blog ID for language function works'
            );
        }
        
        if (function_exists('nm_get_language_url')) {
            $lang_url = nm_get_language_url('en');
            $this->assert_true(
                filter_var($lang_url, FILTER_VALIDATE_URL) !== false, 
                'Language URL generation works'
            );
        }
        
        echo "</div>\n";
    }
    
    /**
     * Test admin functionality
     */
    private function test_admin_functionality() {
        echo "<div class='test-section'><h2>Testing Admin Functionality</h2>\n";
        
        // Test widget class
        $this->assert_true(
            class_exists('NM_Language_Switcher_Widget'), 
            'Language switcher widget class exists'
        );
        
        // Test shortcode registration
        $this->assert_true(
            shortcode_exists('nm_language_switcher'), 
            'Language switcher shortcode registered'
        );
        
        // Test plugin instance creation
        if (method_exists('NativeMind', 'get_instance')) {
            $instance = NativeMind::get_instance();
            $this->assert_true(
                $instance instanceof NativeMind, 
                'Plugin singleton instance works'
            );
        }
        
        echo "</div>\n";
    }
    
    /**
     * Assert that a condition is true
     */
    private function assert_true($condition, $message) {
        $this->total_tests++;
        if ($condition) {
            $this->passed_tests++;
            echo "<span class='test-pass'>✓ PASS:</span> $message<br>\n";
        } else {
            echo "<span class='test-fail'>✗ FAIL:</span> $message<br>\n";
        }
    }
    
    /**
     * Assert that a condition is false
     */
    private function assert_false($condition, $message) {
        $this->assert_true(!$condition, $message);
    }
    
    /**
     * Assert that two values are equal
     */
    private function assert_equals($expected, $actual, $message) {
        $this->assert_true($expected === $actual, "$message (Expected: '$expected', Got: '$actual')");
    }
    
    /**
     * Display test summary
     */
    private function display_summary() {
        echo "<div class='test-section'><h2>Test Summary</h2>\n";
        echo "<p><strong>Total Tests:</strong> {$this->total_tests}</p>\n";
        echo "<p><strong>Passed:</strong> <span class='test-pass'>{$this->passed_tests}</span></p>\n";
        echo "<p><strong>Failed:</strong> <span class='test-fail'>" . ($this->total_tests - $this->passed_tests) . "</span></p>\n";
        
        $success_rate = $this->total_tests > 0 ? round(($this->passed_tests / $this->total_tests) * 100, 2) : 0;
        echo "<p><strong>Success Rate:</strong> {$success_rate}%</p>\n";
        
        if ($success_rate >= 90) {
            echo "<p class='test-pass'>🎉 Excellent! Plugin is working well.</p>\n";
        } elseif ($success_rate >= 70) {
            echo "<p class='test-warning'>⚠️ Good, but some issues need attention.</p>\n";
        } else {
            echo "<p class='test-fail'>❌ Several issues detected. Please review the implementation.</p>\n";
        }
        
        echo "</div>\n";
    }
}

// Run tests if this file is accessed directly
if (basename($_SERVER['PHP_SELF']) === 'test_cache.php') {
    $test = new NativeMindTest();
    $test->run_all_tests();
}