<?php
/**
 * @package NativeMindPlugin
 * @version 1.0
 * 
 * Plugin Name: NativeMind Plugin.
 * Plugin URI: https://nativemind.net
 * Description: Enhances WordPress with advanced translation capabilities and menu management.
 * Version: 1.0.0
 * Author: NativeMind.net (Anton Dodonov)
 * Author URI: https://nativemind.net
 * Text Domain: nativemind
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Don't access directly.
};

require "i18n.php";
require "cache.php";
require "translateTextGoogle.php";
require "include/functions.php";

/**
 * Class NativeMind
 *
 * Main class for the NativeMind plugin. Handles post translations, menu item translations,
 * and other related functionalities.
 */
class NativeMind {
    
    /**
     * Plugin version
     */
    const VERSION = '1.0.0';
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Translation cache
     */
    private $translation_cache = array();
    
    /**
     * Get plugin instance (Singleton pattern)
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * NativeMind constructor.
     *
     * Initializes the plugin, adds necessary filters for content and menu translations.
     */
    public function __construct() {
        // Prevent multiple instances
        if (self::$instance !== null) {
            return self::$instance;
        }
        
        self::$instance = $this;
        
        // Initialize plugin
        $this->init();
    }
    
    /**
     * Initialize plugin hooks and filters
     */
    private function init() {
        // Check if Polylang is active
        if (!nm_is_polylang_active()) {
            add_action('admin_notices', array($this, 'polylang_missing_notice'));
            return;
        }
        
        // Core functionality hooks
        add_filter('the_content', array($this, 'handle_post_translation'), 10);
        add_filter('wp_get_nav_menu_items', array($this, 'translate_menu_items'), 20, 3);
        
        // Additional hooks
        add_filter('the_title', array($this, 'handle_title_translation'), 10, 2);
        add_filter('widget_title', array($this, 'handle_widget_title_translation'), 10, 3);
        add_filter('wp_title', array($this, 'handle_wp_title_translation'), 10, 2);
        
        // Admin hooks
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_nm_clear_cache', array($this, 'ajax_clear_cache'));
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        
        // Plugin activation/deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Display notice when Polylang is missing
     */
    public function polylang_missing_notice() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('NativeMind plugin requires Polylang to be active. Please install and activate Polylang first.', 'nativemind'); ?></p>
        </div>
        <?php
    }

    /**
     * Extract Emoji from string.
     *
     * Extracts an emoji character from the beginning of a given string.
     *
     * @param string $title The string to extract the emoji from.
     *
     * @return string The extracted emoji character or an empty string if no emoji is found.
     */
function get_emoji($title) {
    // Регулярное выражение для поиска эмоджи
    $emoji_regex = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u';

    // Проверяем, начинается ли строка с эмоджи и пробела
    if (preg_match($emoji_regex, $title, $matches)) {
        // Выделяем эмоджи
        $emoji = $matches[0];
	return $emoji;
    }

    return "";
}

    /**
     * Translate Menu Items.
     *
     * Translates menu items, including category titles and special placeholders like #LANGUAGE#.
     * This method handles the translation of menu items, particularly those that are categories.
     * It checks for translated category names and replaces placeholders like #LANGUAGE# with
     * the appropriate language information.
     *
     * @param array $items The menu items to translate.
     * @param stdClass $menu The menu object.
     * @param array $args Additional arguments passed to the menu.
     *
     * @return array The translated menu items.
     */
    public function translate_menu_items($items, $menu, $args) {
	global $nm_languages,$nm_i18n;
        foreach ($items as &$item) {
	    $emoji=$this->get_emoji($item->title);

            // Проверяем, является ли элемент категорией
	    $item->url = wp_make_link_relative($item->url);
	    $current_language=pll_current_language();
	    if ($item->title == "#LANGUAGE#")
	    {
		//$item->title=$current_language;
		$languages = pll_the_languages(array('raw' => 1)); // Получаем список языков
		//$item->title="🌍 ".$languages[$current_language]["name"];
		$item->title=$nm_languages[$current_language];
	    }
	    
            if ($item->type === 'taxonomy' && $item->object === 'category') {
		// Пытаемся получить переведенное название категории
		//$category = get_term_by('id', $item->object_id, 'category');
		$category_id=$item->object_id;
		//$translated_title = $category_id;
		$translated_category_id = pll_get_term($category_id, $language_code);
                $translated_category = get_term_by('id', $translated_category_id , 'category');

                if ($translated_category && !is_wp_error($translated_category)) {
                    // Используем название категории
                    //$item->title = $translated_category->name;
		    //if ($emoji!="")
    			//$item->title = '<span class="menu-item-emoji">' . $emoji . '</span> ' . $item->title;
    			//$item->title =  $emoji . ' ' . $item->title;
		}
                // Иначе смотрим, есть ли локальные переводы
		//    $item->title=$current_language;
//		print_r($nm_languages);
//		print_r($nm_i18n);
//		print_r($nm_i18n[$current_language]);
		//echo($current_language);
		if (is_array($nm_i18n[$current_language])) {
		    //$item->title=$current_language;
		    if ($nm_i18n[$current_language][$item->title]!="") {$item->title=$nm_i18n[$current_language][$item->title];}
		}
            }
        }
        return $items;
    }

    /**
     * Handle Post Translation.
     *
     * Manages the translation of post content. It retrieves the post content in the default language,
     * attempts to retrieve a cached translation for the current language, and if no translation is found,
     * translates the content and caches it for future use.
     *
     * @param string $content The original content of the post.
     *
     * @return string The translated content, or the original content if no translation is available.
     */
    public function handle_post_translation($content) {
        // Get the post ID
        $post_id = get_the_ID();
    
        // Get the current and default languages
        $current_language = pll_current_language();
        $default_language = pll_default_language();
    
        // Get the cache folder path
        $cache_folder_path = get_cache_folder_path();

        $blog_id = get_current_blog_id();
        $network_id = get_current_network_id();

        $original_path = $cache_folder_path."original_{$blog_id}_{$network_id}_{$post_id}.{$default_language}";
        $translated_path = $cache_folder_path."translated_{$blog_id}_{$network_id}_{$post_id}.{$current_language}";
    
    
        // Если пост существует, сохраняем его в оригинальной папке
        if (pll_get_post($post_id, $default_language)) {
            $post = get_post($post_id);
        $content=$post->post_content;
        }

        file_put_contents($original_path, $content);

	//return("TEST".$content);

        // Проверяем, существует ли перевод
        if (file_exists($translated_path)) {
            return file_get_contents($translated_path);
        } else {
            // Здесь ваша логика перевода

            $translated_content = $this->translate($content, $default_language, $current_language);
	    if($translated_content!="") {
                file_put_contents($translated_path, $translated_content);
                return $translated_content;
	    }
        }
	return $content;
    }
    
    /**
     * Handle title translation
     *
     * @param string $title The post title
     * @param int $post_id The post ID
     * @return string Translated title
     */
    public function handle_title_translation($title, $post_id = null) {
        if (empty($title) || !nm_is_polylang_active()) {
            return $title;
        }
        
        $current_language = nm_get_current_language();
        $default_language = nm_get_default_language();
        
        if ($current_language === $default_language) {
            return $title;
        }
        
        // Check cache first
        $cache_key = 'title_' . md5($title . $current_language);
        if (isset($this->translation_cache[$cache_key])) {
            return $this->translation_cache[$cache_key];
        }
        
        $translated_title = $this->translate($title, $default_language, $current_language);
        $this->translation_cache[$cache_key] = $translated_title;
        
        return $translated_title ?: $title;
    }
    
    /**
     * Handle widget title translation
     *
     * @param string $title Widget title
     * @param array $instance Widget instance
     * @param string $id_base Widget ID base
     * @return string Translated title
     */
    public function handle_widget_title_translation($title, $instance = array(), $id_base = '') {
        return $this->handle_title_translation($title);
    }
    
    /**
     * Handle wp_title translation
     *
     * @param string $title Page title
     * @param string $sep Title separator
     * @return string Translated title
     */
    public function handle_wp_title_translation($title, $sep = '|') {
        return $this->handle_title_translation($title);
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_options_page(
            __('NativeMind Settings', 'nativemind'),
            __('NativeMind', 'nativemind'),
            'manage_options',
            'nativemind-settings',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('NativeMind Settings', 'nativemind'); ?></h1>
            
            <div class="card">
                <h2><?php _e('Translation Cache', 'nativemind'); ?></h2>
                <p><?php _e('Manage translation cache to improve performance.', 'nativemind'); ?></p>
                <button type="button" class="button button-secondary" id="nm-clear-cache">
                    <?php _e('Clear Translation Cache', 'nativemind'); ?>
                </button>
                <div id="nm-cache-status"></div>
            </div>
            
            <div class="card">
                <h2><?php _e('Plugin Information', 'nativemind'); ?></h2>
                <table class="widefat">
                    <tr>
                        <td><strong><?php _e('Version', 'nativemind'); ?>:</strong></td>
                        <td><?php echo self::VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Polylang Status', 'nativemind'); ?>:</strong></td>
                        <td><?php echo nm_is_polylang_active() ? __('Active', 'nativemind') : __('Not Active', 'nativemind'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Current Language', 'nativemind'); ?>:</strong></td>
                        <td><?php echo nm_get_current_language(); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Default Language', 'nativemind'); ?>:</strong></td>
                        <td><?php echo nm_get_default_language(); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }
    
    /**
     * AJAX handler for clearing cache
     */
    public function ajax_clear_cache() {
        check_ajax_referer('nm_clear_cache', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'nativemind'));
        }
        
        $cache_folder_path = get_cache_folder_path();
        if ($cache_folder_path && is_dir($cache_folder_path)) {
            $files = glob($cache_folder_path . '*');
            $cleared = 0;
            
            foreach ($files as $file) {
                if (is_file($file) && (strpos($file, 'original_') !== false || strpos($file, 'translated_') !== false)) {
                    unlink($file);
                    $cleared++;
                }
            }
            
            wp_send_json_success(array(
                'message' => sprintf(__('Cleared %d cache files.', 'nativemind'), $cleared)
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Cache directory not found.', 'nativemind')
            ));
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'nativemind-frontend',
            plugin_dir_url(__FILE__) . 'assets/css/frontend.css',
            array(),
            self::VERSION
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ($hook !== 'settings_page_nativemind-settings') {
            return;
        }
        
        wp_enqueue_script(
            'nativemind-admin',
            plugin_dir_url(__FILE__) . 'assets/js/admin.js',
            array('jquery'),
            self::VERSION,
            true
        );
        
        wp_localize_script('nativemind-admin', 'nmAjax', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nm_clear_cache'),
            'messages' => array(
                'clearing' => __('Clearing cache...', 'nativemind'),
                'error' => __('Error occurred while clearing cache.', 'nativemind')
            )
        ));
    }
    
    /**
     * Plugin activation handler
     */
    public function activate() {
        // Create cache directory
        $cache_folder_path = get_cache_folder_path();
        if ($cache_folder_path && !is_dir($cache_folder_path)) {
            wp_mkdir_p($cache_folder_path);
        }
        
        // Set default options
        add_option('nativemind_version', self::VERSION);
        add_option('nativemind_cache_enabled', true);
        add_option('nativemind_auto_translate', true);
    }
    
    /**
     * Plugin deactivation handler
     */
    public function deactivate() {
        // Clean up scheduled events if any
        wp_clear_scheduled_hook('nativemind_cleanup_cache');
    }
    
    /**
     * Translate content with improved caching and error handling
     *
     * @param string $content The content to be translated.
     * @param string $language_from The language code of the original content.
     * @param string $language_to The language code to translate the content into.
     *
     * @return string The translated content, or an empty string if the content could not be translated.
     */
    function translate($content, $language_from, $language_to) {
        // Skip translation if languages are the same
        if ($language_from === $language_to) {
            return $content;
        }
        
        // Skip empty content
        if (empty(trim($content))) {
            return $content;
        }
        
        // Check memory cache first
        $cache_key = md5($content . $language_from . $language_to);
        if (isset($this->translation_cache[$cache_key])) {
            return $this->translation_cache[$cache_key];
        }
        
        try {
            // Use Google Translate API
            $translated_content = translateTextGoogle_nocache($content, $language_to);
            
            // Cache the result
            $this->translation_cache[$cache_key] = $translated_content;
            
            return $translated_content;
        } catch (Exception $e) {
            // Log error and return original content
            error_log('NativeMind translation error: ' . $e->getMessage());
            return $content;
        }
    }
}

// Initialize plugin using singleton pattern
add_action('plugins_loaded', array('NativeMind', 'get_instance'));

// Additional initialization for WordPress hooks
if (!function_exists('nativemind_init')) {
    function nativemind_init() {
        load_plugin_textdomain('nativemind', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    add_action('init', 'nativemind_init');
}