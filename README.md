# ConsentPro

A lightweight, accessible cookie consent banner for WordPress and Craft CMS. GDPR/CCPA compliant with geo-targeting, category-based script blocking, and a two-layer UI design.

## Features

- **Lightweight**: < 5KB gzipped (JS + CSS combined)
- **Two-Layer UI**: Quick actions banner + detailed settings panel
- **Category Control**: Essential, Analytics, Marketing, Personalization
- **Script Blocking**: Block third-party scripts until consent given
- **Dual Storage**: localStorage with cookie fallback (Safari ITP compatible)
- **Geo-Targeting**: EU/CA detection via Cloudflare headers
- **Accessible**: WCAG 2.1 AA compliant with keyboard navigation and screen reader support
- **No Dependencies**: Vanilla JavaScript, no runtime dependencies

## Quick Start

### Demo

```bash
# Clone and install
git clone https://github.com/pxdsgnco/consentpro.git
cd consentpro
pnpm install

# Build the core package
pnpm --filter @consentpro/core build

# Serve the demo
pnpm serve:demo

# Open http://localhost:3000/demo.html
```

### Integration

Add ConsentPro to your HTML:

```html
<!-- CSS in <head> -->
<link rel="stylesheet" href="dist/consentpro.min.css">

<!-- Banner container -->
<aside id="consentpro-banner" class="consentpro"></aside>

<!-- Scripts to block (change type to text/plain) -->
<script type="text/plain" data-consentpro="analytics" src="https://..."></script>
<script type="text/plain" data-consentpro="marketing" src="https://..."></script>

<!-- ConsentPro JS -->
<script src="dist/consentpro.min.js"></script>
<script>
  const storage = new ConsentPro.StorageAdapter();
  const manager = new ConsentPro.ConsentManager(storage);
  const banner = new ConsentPro.BannerUI(manager);

  banner.init('consentpro-banner', {
    policyUrl: '/privacy-policy',
    categories: [
      { id: 'essential', name: 'Essential', description: 'Required for site functionality', required: true },
      { id: 'analytics', name: 'Analytics', description: 'Help us understand usage', required: false },
      { id: 'marketing', name: 'Marketing', description: 'Personalized advertisements', required: false },
      { id: 'personalization', name: 'Personalization', description: 'Remember your preferences', required: false }
    ],
    text: {
      heading: 'We value your privacy',
      description: 'We use cookies to enhance your experience.',
      acceptAll: 'Accept All',
      rejectNonEssential: 'Reject Non-Essential',
      settings: 'Cookie Settings',
      save: 'Save Preferences',
      back: 'Back',
      settingsTitle: 'Privacy Preferences',
      footerToggle: 'Privacy Settings'
    }
  });

  banner.renderFooterToggle();

  if (!manager.isConsentValid()) {
    banner.show();
  }
</script>
```

## Development

### Prerequisites

- Node.js 20+
- pnpm 9.15+

### Commands

```bash
# Install dependencies
pnpm install

# Build all packages
pnpm build

# Run tests
pnpm test

# Run E2E tests
pnpm cypress:run

# Lint
pnpm lint

# Format
pnpm format
```

### Project Structure

```
consentpro/
├── packages/
│   └── consentpro-core/     # Core TypeScript/SCSS package
│       ├── src/
│       │   ├── js/          # TypeScript source
│       │   └── scss/        # SCSS styles
│       ├── dist/            # Built assets
│       └── tests/           # Jest unit tests
├── plugins/
│   ├── consentpro-wp/       # WordPress plugin (Iteration 2)
│   └── consentpro-craft/    # Craft CMS plugin (Iteration 3)
├── cypress/                 # E2E tests
└── docs/                    # Documentation
```

## Events

Listen for consent changes:

```javascript
document.addEventListener('consentpro_consent', (e) => {
  console.log('Consent given:', e.detail.categories);
  // e.detail.categories = { essential: true, analytics: true, ... }
});

document.addEventListener('consentpro_ready', (e) => {
  console.log('ConsentPro initialized');
});
```

## Script Blocking

Mark scripts with `type="text/plain"` and `data-consentpro="category"`:

```html
<!-- Analytics - only runs if analytics category consented -->
<script type="text/plain" data-consentpro="analytics">
  // Google Analytics code
</script>

<!-- Marketing - only runs if marketing category consented -->
<script type="text/plain" data-consentpro="marketing" src="https://pixel.facebook.com/..."></script>
```

## Storage

Consent is stored in:
- **localStorage**: `consentpro_consent` (primary)
- **Cookie**: `consentpro` (fallback for Safari ITP)

Data structure:
```json
{
  "version": 1,
  "timestamp": 1704844800000,
  "geo": "EU",
  "categories": {
    "essential": true,
    "analytics": true,
    "marketing": false,
    "personalization": false
  },
  "hash": "abc123"
}
```

## Configuration

| Option | Type | Description |
|--------|------|-------------|
| `geo` | `'EU' \| 'CA' \| null` | User's detected region |
| `geoEnabled` | `boolean` | Enable geo-based behavior |
| `policyUrl` | `string` | Link to privacy policy |
| `categories` | `CategoryDefinition[]` | Consent categories |
| `text` | `TextConfig` | UI text strings |
| `colors` | `ColorConfig` | Theme colors |

## Accessibility

ConsentPro meets WCAG 2.1 AA requirements:

- `role="dialog"` with `aria-labelledby` and `aria-describedby`
- Category toggles use `role="switch"` with `aria-checked`
- Focus trap when banner is open
- Escape key closes without saving
- Focus returns to trigger element on close
- Live region announces "Preferences saved"
- 44px touch targets, 4.5:1 color contrast

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Documentation

- [Technical Architecture](docs/technical-architecture.md)
- [Product Requirements](docs/high-level-prd.md)
- [Sprint Backlog](docs/refined-backlog.md)

## Roadmap

- **Iteration 1** (Current): Core package with standalone demo
- **Iteration 2**: WordPress plugin with Settings API
- **Iteration 3**: Craft CMS plugin with Control Panel integration
- **Iteration 4**: Advanced features (A/B testing, analytics dashboard)

## License

Proprietary - UNLICENSED
