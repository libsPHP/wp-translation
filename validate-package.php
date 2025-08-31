<?php
/**
 * Simple package validation script for NativeMind WP Translation
 */

echo "Validating NativeMind WP Translation package...\n\n";

// Check required files
$required_files = [
    'composer.json',
    'nativemind.php',
    'i18n.php',
    'cache.php',
    'translateTextGoogle.php',
    'include/functions.php',
    'include/translations.php',
    'README.md',
    'LICENSE.md'
];

$missing_files = [];
foreach ($required_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (!empty($missing_files)) {
    echo "❌ Missing required files:\n";
    foreach ($missing_files as $file) {
        echo "   - $file\n";
    }
    exit(1);
}

echo "✅ All required files present\n";

// Validate composer.json
if (file_exists('composer.json')) {
    $composer_content = file_get_contents('composer.json');
    $composer_json = json_decode($composer_content, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "❌ composer.json contains invalid JSON: " . json_last_error_msg() . "\n";
        exit(1);
    }
    
    // Check required fields
    $required_fields = ['name', 'description', 'type', 'license', 'authors'];
    foreach ($required_fields as $field) {
        if (!isset($composer_json[$field])) {
            echo "❌ composer.json missing required field: $field\n";
            exit(1);
        }
    }
    
    echo "✅ composer.json is valid\n";
    
    // Check package name
    if ($composer_json['name'] !== 'nativemind/wp-translation') {
        echo "❌ Package name should be 'nativemind/wp-translation', found: " . $composer_json['name'] . "\n";
        exit(1);
    }
    
    echo "✅ Package name is correct\n";
}

// Check main plugin file
if (file_exists('nativemind.php')) {
    $plugin_content = file_get_contents('nativemind.php');
    
    // Check for plugin header
    if (strpos($plugin_content, 'Plugin Name:') === false) {
        echo "❌ Main plugin file missing Plugin Name header\n";
        exit(1);
    }
    
    // Check for NativeMind class
    if (strpos($plugin_content, 'class NativeMind') === false) {
        echo "❌ Main plugin file missing NativeMind class\n";
        exit(1);
    }
    
    echo "✅ Main plugin file structure is valid\n";
}

// Check directory structure
$required_dirs = ['include', 'i18n', 'assets', 'tests'];
foreach ($required_dirs as $dir) {
    if (!is_dir($dir)) {
        echo "❌ Missing required directory: $dir\n";
        exit(1);
    }
}

echo "✅ Directory structure is valid\n";

// Check for security issues
$security_files = [
    'translateTextGoogle.php' => ['apiKey', 'API_KEY']
];

foreach ($security_files as $file => $patterns) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        foreach ($patterns as $pattern) {
            if (strpos($content, $pattern) !== false) {
                echo "⚠️  Warning: $file contains potential API key reference: $pattern\n";
                echo "   Make sure to use environment variables or secure configuration\n";
            }
        }
    }
}

echo "\n🎉 Package validation completed successfully!\n";
echo "The package is ready for publication to Packagist.\n\n";

echo "Next steps:\n";
echo "1. Create a GitHub repository\n";
echo "2. Push your code to GitHub\n";
echo "3. Register the package on Packagist.org\n";
echo "4. Set up webhook for automatic updates\n\n";

echo "Package details:\n";
echo "- Name: nativemind/wp-translation\n";
echo "- Type: WordPress Plugin\n";
echo "- License: MIT\n";
echo "- PHP: >=7.4\n";
echo "- WordPress: >=5.0\n";
