# ConsentPro for Craft CMS

GDPR/CCPA-compliant consent banner for Craft CMS with geo-targeting, category-based script blocking, and customizable appearance.

## Requirements

- Craft CMS 5.0+
- PHP 8.2+

## Installation

### Via Composer (Recommended)

1. Add the plugin via Composer:
   ```bash
   composer require consentpro/craft-plugin
   ```

2. Install the plugin from the Control Panel:
   - Go to Settings > Plugins
   - Click "Install" next to ConsentPro

   Or via CLI:
   ```bash
   php craft plugin/install consentpro
   ```

### Manual Installation

1. Download and extract to `vendor/consentpro/craft-plugin/`
2. Run `composer dump-autoload`
3. Install via Control Panel or CLI

## Configuration

Navigate to **Settings > ConsentPro** in the Control Panel.

### General Settings

- **Enable Banner**: Toggle consent banner visibility
- **Privacy Policy URL**: Link to your privacy policy page
- **Geo-Targeting**: Show banner only to EU/CA visitors (requires Cloudflare)

### Appearance

- **Primary Color**: Used for buttons and links
- **Secondary Color**: Used for secondary elements
- **Background Color**: Banner background
- **Text Color**: Banner text

### Consent Categories

- **Essential**: Always enabled (required for site functionality)
- **Analytics**: Tracking and analytics scripts
- **Marketing**: Advertising and remarketing scripts
- **Personalization**: User preference scripts

## Template Usage

### Manual Banner Output

```twig
{{ craft.consentpro.banner() }}
```

### Auto-Inject Assets

Add to your layout template to automatically inject CSS and JavaScript:

```twig
{% do craft.consentpro.autoInject() %}
```

### Check License Status

```twig
{% if craft.consentpro.license.isPro() %}
  {# Pro features available #}
{% endif %}
```

## Script Blocking

Block scripts until consent is given using the `data-consentpro` attribute:

```html
<!-- Analytics script (blocked until analytics consent) -->
<script type="text/plain" data-consentpro="analytics"
        src="https://www.googletagmanager.com/gtag/js?id=GA_ID"></script>

<!-- Marketing script (blocked until marketing consent) -->
<script type="text/plain" data-consentpro="marketing">
  fbq('init', 'PIXEL_ID');
</script>

<!-- Essential scripts always run -->
<script type="text/plain" data-consentpro="essential" src="/js/app.js"></script>
```

## Events

### EVENT_BEFORE_RENDER

Modify banner configuration before output:

```php
use consentpro\consentpro\services\ConsentService;
use yii\base\Event;

Event::on(
    ConsentService::class,
    ConsentService::EVENT_BEFORE_RENDER,
    function (Event $event) {
        $event->config['customField'] = 'value';
    }
);
```

### EVENT_REGISTER_CATEGORIES

Add custom consent categories:

```php
Event::on(
    ConsentService::class,
    ConsentService::EVENT_REGISTER_CATEGORIES,
    function (Event $event) {
        $event->categories[] = [
            'id' => 'social',
            'name' => 'Social Media',
            'description' => 'Enable social sharing features.',
            'required' => false,
        ];
    }
);
```

## Geo-Targeting

ConsentPro uses Cloudflare's `CF-IPCountry` header for geo-detection. When enabled:

- Banner shows to EU visitors (27 member states)
- Banner shows to California (CA) visitors
- Banner hidden for other regions

If the header is missing and geo-targeting is enabled, the banner shows to all visitors (fail-safe for compliance).

## License

Proprietary - ConsentPro Team

For licensing information, visit [consentpro.io](https://consentpro.io)
