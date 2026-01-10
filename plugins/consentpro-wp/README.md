# ConsentPro WordPress Plugin

A GDPR/CCPA-compliant cookie consent banner for WordPress with geo-targeting, category-based script blocking, and customizable appearance.

## Requirements

- WordPress 6.0+
- PHP 7.4+

## Installation

1. Upload the `consentpro` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings → ConsentPro

## Configuration

### General Settings

- **Enable Banner**: Toggle consent banner on/off
- **Privacy Policy URL**: Link to your privacy policy page
- **Geo-Targeting**: Show banner only to EU/CA visitors (requires Cloudflare)

### Appearance Settings

- **Colors**: Primary, secondary, background, and text colors
- **Text**: Customize all banner text strings

### Categories Settings

Configure the four consent categories:

- **Essential**: Always enabled (required for site functionality)
- **Analytics**: Tracking and analytics scripts
- **Marketing**: Advertising and remarketing scripts
- **Personalization**: User preference and customization scripts

## Script Blocking

To block scripts until consent is given, change the script type and add a data attribute:

```html
<!-- Before -->
<script src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>

<!-- After -->
<script
  type="text/plain"
  data-consentpro="analytics"
  src="https://www.googletagmanager.com/gtag/js?id=GA_ID"
></script>
```

Scripts will automatically execute when the user consents to the corresponding category.

## Developer Hooks

ConsentPro provides four filter hooks for customization:

### `consentpro_config`

Modify the banner configuration array before it is output to the frontend.

**Parameters:**

- `$config` (array) - Banner configuration containing geo, geoEnabled, policyUrl, categories, text, and colors.

**Returns:** array

**Example:**

```php
add_filter( 'consentpro_config', function( $config ) {
    // Force geo-targeting to always show banner
    $config['geoEnabled'] = false;

    // Add custom data
    $config['customField'] = 'value';

    return $config;
} );
```

**Example - Override geo detection:**

```php
add_filter( 'consentpro_config', function( $config ) {
    // Use custom geo detection instead of Cloudflare
    $config['geo'] = my_custom_geo_detect();

    return $config;
} );
```

---

### `consentpro_categories`

Modify the consent categories array. Use this to add custom categories or change descriptions.

**Parameters:**

- `$categories` (array) - Array of category objects with id, name, description, and required fields.

**Returns:** array

**Example - Add a custom category:**

```php
add_filter( 'consentpro_categories', function( $categories ) {
    $categories[] = [
        'id'          => 'social',
        'name'        => 'Social Media',
        'description' => 'Enable social media sharing and embedded content.',
        'required'    => false,
    ];

    return $categories;
} );
```

**Example - Modify existing category description:**

```php
add_filter( 'consentpro_categories', function( $categories ) {
    foreach ( $categories as &$category ) {
        if ( $category['id'] === 'analytics' ) {
            $category['description'] = 'We use Google Analytics to understand site usage.';
        }
    }

    return $categories;
} );
```

---

### `consentpro_should_show`

Control whether the consent banner should be displayed. Useful for hiding the banner on specific pages or for certain user roles.

**Parameters:**

- `$should_show` (bool) - Whether to show the banner. Default true.

**Returns:** bool

**Example - Hide on specific pages:**

```php
add_filter( 'consentpro_should_show', function( $should_show ) {
    // Hide on checkout page
    if ( is_page( 'checkout' ) ) {
        return false;
    }

    // Hide for logged-in administrators
    if ( current_user_can( 'manage_options' ) ) {
        return false;
    }

    return $should_show;
} );
```

**Example - Show only on certain post types:**

```php
add_filter( 'consentpro_should_show', function( $should_show ) {
    // Only show on posts and pages
    if ( ! is_singular( [ 'post', 'page' ] ) ) {
        return false;
    }

    return $should_show;
} );
```

---

### `consentpro_assets_url`

Override the base URL for ConsentPro assets (JS and CSS). Useful for serving assets from a CDN.

**Parameters:**

- `$url` (string) - The base URL for assets. Default is the plugin URL.

**Returns:** string

**Example - Serve from CDN:**

```php
add_filter( 'consentpro_assets_url', function( $url ) {
    // Use CDN for production
    if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
        return 'https://cdn.example.com/consentpro/';
    }

    return $url;
} );
```

**Example - Use custom build:**

```php
add_filter( 'consentpro_assets_url', function( $url ) {
    // Serve custom-built assets from theme
    return get_stylesheet_directory_uri() . '/assets/consentpro/';
} );
```

## JavaScript API

After initialization, ConsentPro exposes a global API:

```javascript
// Get current consent state
window.ConsentPro.getConsent();

// Show the banner programmatically
window.ConsentPro.show();

// Listen for consent events
document.addEventListener('consentpro_consent', function (e) {
  console.log('Consent given:', e.detail.categories);
});
```

## Debugging

Enable WordPress debug mode and check for ConsentPro output:

```php
// In wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );

// Check options
var_dump( get_option( 'consentpro_general' ) );
var_dump( get_option( 'consentpro_appearance' ) );
var_dump( get_option( 'consentpro_categories' ) );
```

## License

GPL v2 or later
