---
description: Debugging guides for ConsentPro - browser console, WP, Craft, common issues
globs: ["**/debug*", "**/test*", "**/*.test.ts", "**/*.spec.ts", "**/cypress/**"]
---
# ConsentPro Debugging Reference

## Browser Console

```javascript
// Core API
window.ConsentPro?.show()           // Trigger banner
window.ConsentPro?.getConsent()     // Current state

// Storage inspection
JSON.parse(localStorage.getItem('consentpro_consent'))
document.cookie.split('; ').find(r => r.startsWith('consentpro='))

// Event listeners
document.addEventListener('consentpro_consent', e => console.log(e.detail))
document.addEventListener('consentpro_ready', e => console.log(e.detail))

// Clear for testing
localStorage.removeItem('consentpro_consent')
document.cookie = 'consentpro=; max-age=0; path=/;'
```

## WordPress Debug

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Check options
var_dump(get_option('consentpro_general'));

// Test hooks
add_filter('consentpro_config', function($config) {
    error_log(print_r($config, true));
    return $config;
});
```

## Craft Debug

```php
// config/general.php: 'devMode' => true

// Check settings
\Craft::dump(\Craft::$app->plugins->getPlugin('consentpro')->getSettings());
```

## Common Issues

**Banner not appearing:**
1. Check `#consentpro-banner` has `data-config` attribute
2. Verify assets loaded (Network tab)
3. Check `wp_footer()` called (WP) or auto-inject enabled (Craft)

**Scripts not unblocking:**
1. Verify `type="text/plain"` and `data-consentpro="category"`
2. Check category name is case-sensitive match
3. Confirm consent event fired

**Consent not persisting:**
1. Check localStorage quota
2. Verify not in private/incognito
3. Safari ITP → cookie fallback should work
