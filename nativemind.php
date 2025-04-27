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

/**
 * Class NativeMind
 *
 * Main class for the NativeMind plugin. Handles post translations, menu item translations,
 * and other related functionalities.
 */
class NativeMind {
    /**
     * NativeMind constructor.
     *
     * Initializes the plugin, adds necessary filters for content and menu translations.
     */
    public function __construct() {
        add_filter('the_content', array($this, 'handle_post_translation'));
        add_filter('wp_get_nav_menu_items', array($this, 'translate_menu_items'), 20, 3);
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
     * Translate.
     *
     * Translates the given content from one language to another. This method currently uses
     * Google Translate to perform the translation.
     *
     * @param string $content The content to be translated.
     * @param string $language_from The language code of the original content.
     * @param string $language_to The language code to translate the content into.
     *
     * @return string The translated content, or an empty string if the content could not be translated.
     */
    function translate($content, $language_from, $language_to) {
        /**
         *  Translation logic
         *  Return translated content
         */
	//return "TEST2";

	$content=translateTextGoogle_nocache($content,$language_to);
        return $content;
    }
}

// Инициализация плагина
$native_mind = new NativeMind();