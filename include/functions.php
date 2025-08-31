<?php
/**
 * This file contains custom functions for the NativeLang WordPress plugin.
 *
 * It includes a variety of utility functions used throughout the plugin
 * to enhance its functionality and provide useful tools for developers.
 *
 * @package NativeLang
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Don't access directly.
}

/**
 * Enhanced language detection and switching functionality
 */

/**
 * Get browser language preference
 * 
 * @return string Language code based on browser preference
 */
function nm_get_browser_language() {
    if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        return 'en';
    }
    
    $browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    
    // Map browser language codes to available languages
    $language_map = array(
        'ru' => 'ru',
        'th' => 'th', 
        'zh' => 'zh',
        'hi' => 'hi',
        'en' => 'en'
    );
    
    return isset($language_map[$browser_lang]) ? $language_map[$browser_lang] : 'en';
}

/**
 * Auto-redirect to appropriate language site
 */
function nm_auto_redirect_language() {
    // Don't redirect if already on a language-specific domain
    if (nm_get_language_for_domain() !== 'en') {
        return;
    }
    
    // Don't redirect bots or if already redirected
    if (is_admin() || wp_doing_ajax() || wp_doing_cron() || isset($_COOKIE['nm_no_redirect'])) {
        return;
    }
    
    $browser_lang = nm_get_browser_language();
    
    if ($browser_lang !== 'en' && function_exists('nm_get_language_url')) {
        $redirect_url = nm_get_language_url($browser_lang, $_SERVER['REQUEST_URI']);
        
        // Set cookie to prevent redirect loops
        setcookie('nm_no_redirect', '1', time() + 3600, '/');
        
        wp_redirect($redirect_url, 302);
        exit;
    }
}

/**
 * Generate language switcher HTML
 * 
 * @param array $args Switcher configuration
 * @return string HTML for language switcher
 */
function nm_get_language_switcher($args = array()) {
    $defaults = array(
        'show_flags' => true,
        'show_names' => true,
        'current_lang' => nm_get_current_language(),
        'class' => 'nativemind-language-switcher',
        'dropdown' => false
    );
    
    $args = wp_parse_args($args, $defaults);
    
    if (!function_exists('pll_the_languages')) {
        return '';
    }
    
    $languages = pll_the_languages(array('raw' => 1));
    
    if (empty($languages)) {
        return '';
    }
    
    $output = '<div class="' . esc_attr($args['class']) . '">';
    
    if ($args['dropdown']) {
        $output .= '<select onchange="window.location.href=this.value">';
        
        foreach ($languages as $lang) {
            $selected = ($lang['slug'] === $args['current_lang']) ? ' selected' : '';
            $flag = $args['show_flags'] ? nm_get_flag_emoji($lang['slug']) . ' ' : '';
            $name = $args['show_names'] ? $lang['name'] : $lang['slug'];
            
            $output .= '<option value="' . esc_url($lang['url']) . '"' . $selected . '>';
            $output .= $flag . esc_html($name);
            $output .= '</option>';
        }
        
        $output .= '</select>';
    } else {
        $output .= '<ul>';
        
        foreach ($languages as $lang) {
            $current_class = ($lang['slug'] === $args['current_lang']) ? ' current-language' : '';
            $flag = $args['show_flags'] ? nm_get_flag_emoji($lang['slug']) . ' ' : '';
            $name = $args['show_names'] ? $lang['name'] : $lang['slug'];
            
            $output .= '<li class="lang-' . esc_attr($lang['slug']) . $current_class . '">';
            $output .= '<a href="' . esc_url($lang['url']) . '" hreflang="' . esc_attr($lang['slug']) . '">';
            $output .= $flag . esc_html($name);
            $output .= '</a></li>';
        }
        
        $output .= '</ul>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Get flag emoji for language code
 * 
 * @param string $lang_code Language code
 * @return string Flag emoji
 */
function nm_get_flag_emoji($lang_code) {
    $flags = array(
        'en' => '🇺🇸',
        'ru' => '🇷🇺', 
        'th' => '🇹🇭',
        'zh' => '🇨🇳',
        'hi' => '🇮🇳',
        'es' => '🇪🇸',
        'fr' => '🇫🇷',
        'de' => '🇩🇪',
        'it' => '🇮🇹',
        'pt' => '🇵🇹',
        'ja' => '🇯🇵',
        'ko' => '🇰🇷'
    );
    
    return isset($flags[$lang_code]) ? $flags[$lang_code] : '🌐';
}

/**
 * Translation quality checker
 * 
 * @param string $original Original text
 * @param string $translated Translated text
 * @return array Quality metrics
 */
function nm_check_translation_quality($original, $translated) {
    $metrics = array(
        'length_ratio' => 0,
        'word_count_ratio' => 0,
        'has_html' => false,
        'html_preserved' => true,
        'quality_score' => 0
    );
    
    // Length ratio
    $orig_len = strlen($original);
    $trans_len = strlen($translated);
    
    if ($orig_len > 0) {
        $metrics['length_ratio'] = $trans_len / $orig_len;
    }
    
    // Word count ratio  
    $orig_words = str_word_count($original);
    $trans_words = str_word_count($translated);
    
    if ($orig_words > 0) {
        $metrics['word_count_ratio'] = $trans_words / $orig_words;
    }
    
    // HTML preservation check
    $metrics['has_html'] = (strip_tags($original) !== $original);
    
    if ($metrics['has_html']) {
        $orig_tags = nm_extract_html_tags($original);
        $trans_tags = nm_extract_html_tags($translated);
        $metrics['html_preserved'] = ($orig_tags === $trans_tags);
    }
    
    // Calculate quality score (0-100)
    $score = 100;
    
    // Penalize extreme length differences
    if ($metrics['length_ratio'] < 0.3 || $metrics['length_ratio'] > 3.0) {
        $score -= 30;
    }
    
    // Penalize extreme word count differences
    if ($metrics['word_count_ratio'] < 0.5 || $metrics['word_count_ratio'] > 2.0) {
        $score -= 20;
    }
    
    // Penalize HTML tag loss
    if ($metrics['has_html'] && !$metrics['html_preserved']) {
        $score -= 25;
    }
    
    // Penalize empty translations
    if (empty(trim($translated))) {
        $score = 0;
    }
    
    $metrics['quality_score'] = max(0, $score);
    
    return $metrics;
}

/**
 * Extract HTML tags from content
 * 
 * @param string $content Content with HTML
 * @return array Array of HTML tags
 */
function nm_extract_html_tags($content) {
    preg_match_all('/<[^>]+>/', $content, $matches);
    return $matches[0];
}

/**
 * Clean translation text
 * 
 * @param string $text Text to clean
 * @return string Cleaned text
 */
function nm_clean_translation_text($text) {
    // Remove excessive whitespace
    $text = preg_replace('/\s+/', ' ', $text);
    
    // Fix common translation artifacts
    $text = str_replace(array('& amp;', '& quot;', '& #039;'), array('&amp;', '&quot;', '&#039;'), $text);
    
    // Trim whitespace
    $text = trim($text);
    
    return $text;
}

/**
 * Shortcode for language switcher
 * 
 * @param array $atts Shortcode attributes
 * @return string Language switcher HTML
 */
function nm_language_switcher_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_flags' => 'true',
        'show_names' => 'true', 
        'dropdown' => 'false',
        'class' => 'nativemind-language-switcher'
    ), $atts);
    
    // Convert string booleans
    $atts['show_flags'] = ($atts['show_flags'] === 'true');
    $atts['show_names'] = ($atts['show_names'] === 'true');
    $atts['dropdown'] = ($atts['dropdown'] === 'true');
    
    return nm_get_language_switcher($atts);
}
add_shortcode('nm_language_switcher', 'nm_language_switcher_shortcode');

/**
 * Widget for language switcher
 */
class NM_Language_Switcher_Widget extends WP_Widget {
    
    public function __construct() {
        parent::__construct(
            'nm_language_switcher',
            __('NativeMind Language Switcher', 'nativemind'),
            array('description' => __('Display a language switcher for multilingual sites.', 'nativemind'))
        );
    }
    
    public function widget($args, $instance) {
        echo $args['before_widget'];
        
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        
        $switcher_args = array(
            'show_flags' => !empty($instance['show_flags']),
            'show_names' => !empty($instance['show_names']),
            'dropdown' => !empty($instance['dropdown']),
            'class' => 'widget-language-switcher'
        );
        
        echo nm_get_language_switcher($switcher_args);
        
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $show_flags = !empty($instance['show_flags']);
        $show_names = !empty($instance['show_names']);
        $dropdown = !empty($instance['dropdown']);
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title:', 'nativemind'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_flags); ?> id="<?php echo esc_attr($this->get_field_id('show_flags')); ?>" name="<?php echo esc_attr($this->get_field_name('show_flags')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_flags')); ?>"><?php _e('Show flags', 'nativemind'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($show_names); ?> id="<?php echo esc_attr($this->get_field_id('show_names')); ?>" name="<?php echo esc_attr($this->get_field_name('show_names')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('show_names')); ?>"><?php _e('Show language names', 'nativemind'); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked($dropdown); ?> id="<?php echo esc_attr($this->get_field_id('dropdown')); ?>" name="<?php echo esc_attr($this->get_field_name('dropdown')); ?>">
            <label for="<?php echo esc_attr($this->get_field_id('dropdown')); ?>"><?php _e('Use dropdown', 'nativemind'); ?></label>
        </p>
        <?php
    }
    
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['show_flags'] = (!empty($new_instance['show_flags'])) ? 1 : 0;
        $instance['show_names'] = (!empty($new_instance['show_names'])) ? 1 : 0;
        $instance['dropdown'] = (!empty($new_instance['dropdown'])) ? 1 : 0;
        
        return $instance;
    }
}

// Register the widget
function nm_register_widgets() {
    register_widget('NM_Language_Switcher_Widget');
}
add_action('widgets_init', 'nm_register_widgets');
