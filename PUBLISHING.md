# Publishing Guide for nativemind/wp-translation

This guide explains how to publish the NativeMind WP Translation plugin to Packagist.

## Package Overview

- **Package Name**: `nativemind/wp-translation`
- **Type**: WordPress Plugin
- **License**: MIT
- **PHP Version**: >=7.4
- **WordPress Version**: >=5.0

## Prerequisites

1. **Composer installed** on your system
2. **GitHub account** for hosting the source code
3. **Packagist account** for publishing the package
4. **Git repository** with the plugin code

## Step-by-Step Publishing Process

### 1. Create GitHub Repository

```bash
# Initialize git repository (if not already done)
git init

# Add all files
git add .

# Create initial commit
git commit -m "Initial release of NativeMind WP Translation v1.0.0"

# Add remote repository (replace with your GitHub repo URL)
git remote add origin https://github.com/nativemind/wp-translation.git

# Push to GitHub
git push -u origin main
```

### 2. Create GitHub Release

1. Go to your GitHub repository
2. Click "Releases" → "Create a new release"
3. Set tag version: `v1.0.0`
4. Set release title: `NativeMind WP Translation v1.0.0`
5. Add release description from CHANGELOG.md
6. Publish the release

### 3. Register on Packagist

1. Go to [packagist.org](https://packagist.org)
2. Sign up or log in
3. Click "Submit" in the top menu
4. Enter your repository URL: `https://github.com/nativemind/wp-translation`
5. Click "Check"
6. Review the package information
7. Click "Submit"

### 4. Configure Packagist Webhook (Recommended)

1. In your Packagist package page, copy the webhook URL
2. Go to your GitHub repository settings
3. Navigate to "Webhooks" → "Add webhook"
4. Paste the Packagist webhook URL
5. Set content type to "application/json"
6. Select "Just the push event"
7. Click "Add webhook"

This ensures automatic updates when you push new releases.

## Package Structure Validation

The package includes these essential files:

```
nativemind-wp-translation/
├── composer.json              # Package configuration
├── nativemind.php            # Main plugin file
├── i18n.php                  # Internationalization
├── cache.php                 # Caching system
├── translateTextGoogle.php   # Google Translate integration
├── include/
│   ├── functions.php         # Helper functions
│   └── translations.php      # Translation mappings
├── i18n/                     # Language files
├── assets/                   # CSS and JS files
├── tests/                    # Test suite
├── README.md                 # Documentation
├── CHANGELOG.md              # Version history
├── LICENSE.md                # MIT License
└── .gitignore                # Git ignore rules
```

## Installation Instructions for Users

Once published, users can install the plugin via Composer:

```bash
composer require nativemind/wp-translation
```

Or add to their `composer.json`:

```json
{
    "require": {
        "nativemind/wp-translation": "^1.0"
    }
}
```

## Updating the Package

### For New Versions

1. Update version in `composer.json` and `nativemind.php`
2. Update `CHANGELOG.md`
3. Create new Git tag and GitHub release
4. Push changes - Packagist will auto-update via webhook

### Manual Update (if webhook fails)

1. Go to your Packagist package page
2. Click "Update" button
3. Wait for the update to complete

## Security Considerations

⚠️ **Important**: The `translateTextGoogle.php` file contains API key references. Before publishing:

1. Create a template file: `translateTextGoogle.php.example`
2. Remove actual API keys from the published version
3. Add instructions in README for API key configuration
4. Use environment variables in production

## Package Information

### Composer Configuration

The `composer.json` includes:
- Proper autoloading for WordPress plugins
- Required PHP extensions (json, curl)
- Development dependencies for testing
- WordPress plugin metadata
- Support information and links

### Dependencies

- **Required**: PHP 7.4+, Polylang plugin
- **Suggested**: Google Cloud Translate library
- **Development**: PHPUnit, WordPress Coding Standards

## Troubleshooting

### Common Issues

1. **Package not found**: Ensure GitHub repository is public
2. **Webhook not working**: Check GitHub webhook configuration
3. **Version not updating**: Manually trigger Packagist update
4. **Installation fails**: Verify all required files are included

### Support

- **Issues**: GitHub Issues
- **Documentation**: nativemind.net/docs/wp-translation
- **Email**: support@nativemind.net

## Next Steps After Publishing

1. **Documentation**: Update website documentation
2. **Marketing**: Announce on WordPress communities
3. **Support**: Monitor GitHub issues and provide support
4. **Updates**: Plan future feature releases
5. **Integration**: Consider WordPress.org plugin directory submission

---

**Package URL**: https://packagist.org/packages/nativemind/wp-translation
**GitHub Repository**: https://github.com/nativemind/wp-translation
**Documentation**: https://nativemind.net/docs/wp-translation
