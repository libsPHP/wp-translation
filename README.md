# NativeLang WordPress Plugin

**Empowering Multilingual WordPress Sites with Advanced Translation Capabilities**

NativeLang is a comprehensive WordPress plugin designed to significantly enhance your website's multilingual capabilities. Built as an advanced extension for the popular Polylang plugin, NativeLang adds powerful translation features, intelligent caching, and seamless multisite support that make it easier than ever to manage and deliver content in multiple languages.

## 🚀 Key Features

### Core Translation Features
- **🔄 Automatic Content Translation:** Real-time translation of posts, pages, titles, and widgets using Google Translate API
- **🎯 Smart Menu Translation:** Automatic translation of navigation menus with emoji support and custom mappings
- **🧠 Intelligent Caching:** Advanced caching system with automatic expiration and cleanup to reduce API calls
- **📊 Translation Quality Control:** Built-in quality assessment with metrics and error handling

### User Interface & Experience
- **🌍 Language Switcher Widget:** Customizable language switcher with flags, dropdowns, and multiple display options
- **🎨 Emoji Support:** Rich emoji integration for enhanced visual language identification
- **📱 Responsive Design:** Mobile-optimized interface with accessibility features
- **⚡ Real-time Updates:** Live translation status with progress indicators

### Advanced Features
- **🏢 Multisite Support:** Full WordPress Multisite compatibility with domain mapping
- **🔧 Developer-Friendly:** Extensive hooks, filters, and APIs for customization
- **📈 Performance Optimized:** Minimal database queries with intelligent memory management
- **🛡️ Security Focused:** Secure cache handling with proper sanitization and validation

### Administrative Tools
- **⚙️ Admin Dashboard:** Comprehensive settings page with cache management and statistics
- **📊 Analytics:** Detailed translation metrics and performance monitoring
- **🔄 Bulk Operations:** Mass cache clearing and translation management
- **🧪 Testing Suite:** Built-in test framework for validation and debugging

## 📦 Architecture Overview

NativeLang follows a modular architecture designed for performance, maintainability, and extensibility:

### Core Components
- **🏗️ Main Plugin Class (`NativeMind`):** Singleton pattern with comprehensive hook management
- **💾 Cache System (`NativeMindCache`):** Advanced file-based caching with automatic cleanup
- **🌐 Internationalization (`i18n.php`):** Complete language support with Polylang integration
- **🔧 Utility Functions (`functions.php`):** Helper functions for common operations
- **🌍 Multisite Support (`multisite/`):** Domain mapping and multi-language site management

### Integration Points
- **WordPress Core:** Deep integration with WordPress hooks and filters
- **Polylang Plugin:** Seamless extension of Polylang's capabilities
- **Google Translate API:** Reliable translation service with fallback handling
- **WordPress Multisite:** Native support for network installations

## 🛠️ Installation & Setup

### Prerequisites
- WordPress 5.0 or higher
- PHP 7.4 or higher
- Polylang plugin installed and activated
- Google Translate API key (for automatic translations)

### Installation Steps

1. **Upload Plugin Files:**
   ```bash
   # Upload to your WordPress plugins directory
   /wp-content/plugins/nativelang-wordpress/
   ```

2. **Configure Polylang:**
   - Install and activate Polylang
   - Set up your languages in Polylang settings
   - Configure default language

3. **Activate NativeLang:**
   - Go to WordPress Admin → Plugins
   - Activate "NativeMind Plugin"

4. **Configure API Key:**
   ```php
   // Edit translateTextGoogle.php
   $apiKey = "YOUR_GOOGLE_TRANSLATE_API_KEY";
   ```

5. **Multisite Setup (Optional):**
   ```php
   // Edit multisite/my_domains.php
   cybo_add_extra_domain( 'your-domain.com', '/', 1 );
   ```

## 🎯 Usage Guide

### Basic Usage

#### Language Switcher
Add language switcher anywhere using:

**Shortcode:**
```php
[nm_language_switcher show_flags="true" show_names="true" dropdown="false"]
```

**PHP Function:**
```php
echo nm_get_language_switcher(array(
    'show_flags' => true,
    'show_names' => true,
    'dropdown' => false
));
```

**Widget:**
- Go to Appearance → Widgets
- Add "NativeMind Language Switcher" widget

#### Menu Translation
- Create menus in WordPress as usual
- Use `#LANGUAGE#` placeholder for current language display
- Add emojis to menu items for visual enhancement

### Advanced Configuration

#### Custom Translation Mappings
```php
// Add to i18n/menu/custom.php
$nm_custom = array(
    'en' => array(
        'Home' => 'Home',
        'About' => 'About Us'
    ),
    'ru' => array(
        'Home' => 'Главная',
        'About' => 'О нас'
    )
);
```

#### Cache Management
```php
// Clear all cache
$cleared = NativeMindCache::clear_all();

// Get cache statistics  
$stats = NativeMindCache::get_stats();

// Manual cache control
$cache_key = NativeMindCache::generate_cache_key($content, 'en', 'ru');
NativeMindCache::set($cache_key, $translated_content);
```

#### Multisite Domain Mapping
```php
// Configure in multisite/my_domains.php
cybo_add_extra_domain( 'en.example.com', '/', 1 );  // English site
cybo_add_extra_domain( 'ru.example.com', '/', 2 );  // Russian site
cybo_add_extra_domain( 'example.ru', '/', 2 );      // Alternative Russian domain
```

## 🔧 Developer API

### Hooks and Filters

#### Actions
```php
// Plugin initialization
do_action('nm_plugin_loaded');

// Translation events
do_action('nm_translation_start', $content, $from_lang, $to_lang);
do_action('nm_translation_complete', $translated_content, $original_content);
do_action('nm_translation_error', $error_message, $content);

// Cache events
do_action('nm_cache_cleared');
do_action('nm_cache_cleanup');
```

#### Filters
```php
// Modify translation before processing
$content = apply_filters('nm_pre_translate', $content, $from_lang, $to_lang);

// Modify translated content
$translated = apply_filters('nm_post_translate', $translated_content, $original_content);

// Customize language switcher output
$switcher_html = apply_filters('nm_language_switcher_html', $html, $args);

// Modify cache key generation
$cache_key = apply_filters('nm_cache_key', $cache_key, $content, $lang_from, $lang_to);
```

### Custom Functions

#### Translation Quality Check
```php
$quality = nm_check_translation_quality($original, $translated);
echo "Quality Score: " . $quality['quality_score'] . "%";
```

#### Browser Language Detection
```php
$browser_lang = nm_get_browser_language();
if ($browser_lang !== 'en') {
    // Redirect to appropriate language site
    nm_auto_redirect_language();
}
```

#### Custom Flag Emojis
```php
$flag = nm_get_flag_emoji('de'); // Returns 🇩🇪
```

## 🧪 Testing

### Test Suite
Run the comprehensive test suite:
```php
// Access via browser
http://yoursite.com/wp-content/plugins/nativelang-wordpress/test_cache.php

// Or include in code
include 'test_cache.php';
$test = new NativeMindTest();
$test->run_all_tests();
```

### Manual Testing Checklist
- [ ] Plugin activation without errors
- [ ] Polylang integration working
- [ ] Language switcher displays correctly
- [ ] Menu translations functional
- [ ] Cache operations working
- [ ] Admin dashboard accessible
- [ ] Multisite domain mapping (if applicable)

## 📊 Performance & Optimization

### Caching Strategy
- **File-based caching** with automatic expiration (7 days default)
- **Memory caching** for repeated translations within single request
- **Intelligent cache keys** incorporating blog ID, network ID, and content hash
- **Automatic cleanup** via WordPress cron jobs

### Performance Tips
1. **API Key Management:** Use environment variables for API keys
2. **Cache Tuning:** Adjust cache expiration based on content update frequency
3. **Selective Translation:** Don't translate administrative content
4. **CDN Integration:** Serve static assets via CDN for better performance

## 🛡️ Security Considerations

### Data Protection
- All user input is properly sanitized and validated
- Cache files are protected with .htaccess rules
- API keys should be stored securely (not in version control)
- CSRF protection on all admin actions

### Best Practices
- Regular cache cleanup to prevent disk space issues
- Monitor API usage to avoid quota limits
- Use HTTPS for all translation API calls
- Implement proper error handling and logging

## 🐛 Troubleshooting

### Common Issues

#### "Polylang not found" Error
**Solution:** Install and activate Polylang plugin first.

#### Translations Not Appearing
**Possible Causes:**
- Invalid Google Translate API key
- Network connectivity issues
- Cache directory permissions
- Polylang configuration incomplete

**Debug Steps:**
1. Check API key validity
2. Verify cache directory is writable
3. Enable WordPress debug logging
4. Run test suite for diagnostics

#### Language Switcher Not Displaying
**Possible Causes:**
- Theme conflicts
- CSS styling issues
- Polylang languages not configured

**Solutions:**
1. Check browser console for errors
2. Verify Polylang language setup
3. Add custom CSS if needed
4. Test with default theme

### Debug Mode
Enable debug mode for detailed logging:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check logs at /wp-content/debug.log
```

## 🤝 Contributing

We welcome contributions! Please see our [GitHub repository](https://github.com/taxlien-online/nativelang-wordpress) for:
- Issue reporting
- Feature requests  
- Pull request guidelines
- Development setup instructions

## 📄 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Acknowledgments

- **Polylang Team** for the excellent multilingual foundation
- **Google Translate** for reliable translation services
- **WordPress Community** for the robust platform
- **Contributors** who help improve this plugin

## 📞 Support

- **Documentation:** [nativemind.net](https://nativemind.net)
- **Email Support:** support@nativemind.net
- **Community Forum:** [WordPress.org Plugin Forum](https://wordpress.org/support/plugin/nativelang)
- **Professional Support:** Available for enterprise implementations

---

**Made with ❤️ by [NativeMind.net](https://nativemind.net) | Part of the [TaxLien.online](https://taxlien.online) project**

