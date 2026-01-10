# @consentpro/core

Core consent banner logic and styles for ConsentPro. This package provides the JavaScript and CSS needed to render and manage a GDPR/CCPA compliant cookie consent banner.

## Installation

```bash
# Via pnpm (within monorepo)
pnpm add @consentpro/core

# Or copy built assets directly
dist/consentpro.min.js   # IIFE bundle
dist/consentpro.esm.js   # ES module
dist/consentpro.min.css  # Styles
dist/consentpro.d.ts     # TypeScript types
```

## Bundle Size

| Asset              | Size        | Gzipped    |
| ------------------ | ----------- | ---------- |
| consentpro.min.js  | 9.8 KB      | 3.1 KB     |
| consentpro.min.css | 6.0 KB      | 1.7 KB     |
| **Total**          | **15.8 KB** | **4.8 KB** |

## Usage

### Basic Setup

```html
<!DOCTYPE html>
<html>
  <head>
    <link rel="stylesheet" href="consentpro.min.css" />
  </head>
  <body>
    <!-- Banner container -->
    <aside id="consentpro-banner" class="consentpro"></aside>

    <script src="consentpro.min.js"></script>
    <script>
      const storage = new ConsentPro.StorageAdapter();
      const manager = new ConsentPro.ConsentManager(storage);
      const banner = new ConsentPro.BannerUI(manager);

      banner.init('consentpro-banner', {
        policyUrl: '/privacy',
        categories: [
          {
            id: 'essential',
            name: 'Essential',
            description: 'Required for site functionality',
            required: true,
          },
          {
            id: 'analytics',
            name: 'Analytics',
            description: 'Help us understand usage',
            required: false,
          },
          { id: 'marketing', name: 'Marketing', description: 'Personalized ads', required: false },
          {
            id: 'personalization',
            name: 'Personalization',
            description: 'Remember preferences',
            required: false,
          },
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
          footerToggle: 'Privacy Settings',
        },
      });

      banner.renderFooterToggle();

      if (!manager.isConsentValid()) {
        banner.show();
      }
    </script>
  </body>
</html>
```

### ES Module Import

```javascript
import { ConsentManager, StorageAdapter, BannerUI } from '@consentpro/core';

const storage = new StorageAdapter();
const manager = new ConsentManager(storage);
const banner = new BannerUI(manager);
```

## API Reference

### ConsentManager

Manages consent state, storage, and events.

```typescript
class ConsentManager {
  constructor(storage?: StorageAdapter);

  // Initialize with configuration
  init(config: BannerConfig): void;

  // Get current consent data
  getConsent(): ConsentData | null;

  // Set consent with categories
  setConsent(categories: Partial<ConsentCategories>): void;

  // Check if consent is valid (not expired, config unchanged)
  isConsentValid(): boolean;

  // Clear stored consent
  clearConsent(): void;
}
```

### StorageAdapter

Handles localStorage with cookie fallback for Safari ITP.

```typescript
class StorageAdapter {
  // Get consent from storage (localStorage first, cookie fallback)
  get(): ConsentData | null;

  // Set consent to both localStorage and cookie
  set(data: ConsentData): void;

  // Clear both storage types
  clear(): void;
}
```

### BannerUI

Renders the consent banner UI.

```typescript
class BannerUI {
  constructor(manager: ConsentManager);

  // Initialize with container ID and config
  init(containerId: string, config: BannerConfig): void;

  // Show banner (Layer 1)
  show(): void;

  // Hide banner
  hide(): void;

  // Get current consent
  getConsent(): ConsentData | null;

  // Render footer toggle button
  renderFooterToggle(containerId?: string): void;
}
```

### GeoDetector

Detects user region from server-provided data.

```typescript
class GeoDetector {
  constructor(config: BannerConfig);

  // Get detected region
  getGeo(): 'EU' | 'CA' | null;

  // Check if banner should be shown based on geo
  shouldShowBanner(): boolean;
}
```

## Types

```typescript
interface ConsentData {
  version: number;
  timestamp: number;
  geo: 'EU' | 'CA' | null;
  categories: ConsentCategories;
  hash: string;
}

interface ConsentCategories {
  essential: true;
  analytics: boolean;
  marketing: boolean;
  personalization: boolean;
}

interface CategoryDefinition {
  id: string;
  name: string;
  description: string;
  required: boolean;
}

interface BannerConfig {
  geo: 'EU' | 'CA' | null;
  geoEnabled: boolean;
  policyUrl: string;
  categories: CategoryDefinition[];
  text: TextConfig;
  colors?: ColorConfig;
}
```

## Events

```javascript
// Fired when consent is given/updated
document.addEventListener('consentpro_consent', (e) => {
  const { categories, timestamp, geo } = e.detail;
  console.log('Consent:', categories);
});

// Fired when ConsentPro is initialized
document.addEventListener('consentpro_ready', (e) => {
  const { config } = e.detail;
  console.log('Ready with config:', config);
});
```

## Script Blocking

Block scripts until category consent is given:

```html
<!-- Blocked until analytics consent -->
<script type="text/plain" data-consentpro="analytics" src="ga.js"></script>

<!-- Blocked until marketing consent -->
<script type="text/plain" data-consentpro="marketing">
  fbq('init', '123');
</script>
```

## Customization

### Colors

```javascript
banner.init('container', {
  // ...
  colors: {
    primary: '#2563eb', // Primary button, accents
    secondary: '#64748b', // Secondary buttons
    background: '#ffffff', // Banner background
    text: '#1e293b', // Text color
  },
});
```

### Text

All UI text is customizable via the `text` config option.

## Development

```bash
# Install dependencies
pnpm install

# Build
pnpm build

# Watch mode
pnpm dev

# Run tests
pnpm test

# Run tests with coverage
pnpm test:coverage

# Lint
pnpm lint

# Type check
pnpm typecheck
```

## Testing

- **Unit tests**: Jest with jsdom
- **Coverage target**: 80% branches, 90% lines
- **E2E tests**: Cypress (run from monorepo root)

```bash
# Unit tests
pnpm test

# E2E tests (from monorepo root)
pnpm cypress:run
```

## Browser Support

- Chrome 80+
- Firefox 75+
- Safari 13+ (with cookie fallback for ITP)
- Edge 80+

## License

Proprietary - UNLICENSED
