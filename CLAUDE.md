# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

ConsentPro is a cross-platform consent banner solution for WordPress and Craft CMS that combines privacy policy acknowledgment, cookie consent, and category-based script blocking into a single two-layer banner experience.

**Architecture:** Monorepo with shared TypeScript/SCSS core (`@consentpro/core`) consumed by platform-specific plugins (WordPress, Craft CMS).

**Key Goals:**
- Bundle size: <5KB gzipped (JS + CSS combined)
- GDPR/CCPA/ePrivacy compliant
- Geo-targeted (EU/CA) via Cloudflare headers
- localStorage + cookie fallback for Safari ITP
- Category-based script blocking (essential, analytics, marketing, personalization)

## Claude Code Rules
@import .claude/rules/general.md
@import .claude/rules/typescript.md  # Iteration 1 core
@import .claude/rules/scss.md        # Iteration 1 styles
@import .claude/rules/php-wp.md      # Iteration 2 WP
@import .claude/rules/php-craft.md   # Iteration 3 Craft

## Development Commands

### Core Package (`packages/consentpro-core/`)
```bash
# Install dependencies
pnpm install

# Build TypeScript + SCSS to dist/
pnpm build

# Watch mode for development
pnpm dev

# Run tests
pnpm test

# Lint
pnpm lint
```

### WordPress Plugin (`plugins/consentpro-wp/`)
```bash
# Install Composer dependencies
composer install

# Run PHPUnit tests
composer test

# Code standards check
composer phpcs
```

### Craft CMS Plugin (`plugins/consentpro-craft/`)
```bash
# Install Composer dependencies
composer install

# Run Codeception tests
composer test
```

## Core Architecture

### Component Hierarchy

```
@consentpro/core
├── ConsentManager         # State management, storage interface, events
├── StorageAdapter         # localStorage + cookie dual-write/read
├── BannerUI              # DOM rendering for Layer 1 (quick actions) and Layer 2 (settings panel)
├── GeoDetector           # Reads geo from server-injected data attributes
└── Script Blocker        # Activates scripts based on category consent
```

### Data Flow

1. **Server-side** (WP/Craft): Reads `CF-IPCountry` header → renders banner container with `data-config` JSON
2. **Client init**: Core reads config, checks localStorage/cookie for existing consent
3. **If no consent**: Show Layer 1 banner (Accept All / Reject Non-Essential / Settings)
4. **Layer 2** (settings panel): Category toggles + Save Preferences
5. **On consent**: Fire `consentpro_consent` CustomEvent → unblock matching category scripts
6. **Persistence**: Store in localStorage (primary) + cookie (Safari ITP fallback), 12-month retention

### Consent Storage Schema

```typescript
// localStorage key: 'consentpro_consent'
interface ConsentData {
  version: number;          // Schema version for migrations
  timestamp: number;        // Unix ms when consent given
  geo: string | null;       // 'EU' | 'CA' | null
  categories: {
    essential: true;        // Always true, immutable
    analytics: boolean;
    marketing: boolean;
    personalization: boolean;
  };
  hash: string;            // SHA-256 of settings (detect config changes → re-consent)
}
```

### Script Blocking Pattern

Scripts must use `type="text/plain"` with `data-consentpro="category"` attribute:

```html
<!-- Will execute only if 'analytics' category consented -->
<script type="text/plain" data-consentpro="analytics" src="https://..."></script>

<!-- Essential scripts always execute -->
<script type="text/plain" data-consentpro="essential">...</script>
```

Core JS changes `type` to `text/javascript` when category consented, triggering execution.

## Platform Integration

### WordPress Plugin

**File structure:**
- `consentpro.php` - Main plugin file with header
- `includes/class-consentpro.php` - Main plugin class
- `admin/class-settings.php` - Settings API registration (5 tabs: General, Appearance, Categories, Consent Log, License)
- `public/class-banner.php` - Frontend injection via `wp_footer` hook
- `includes/class-license.php` - Remote license validation with cron

**Key hooks:**
- `consentpro_config` - Modify config array before output
- `consentpro_categories` - Add/modify consent categories
- `consentpro_should_show` - Override banner visibility logic
- `consentpro_assets_url` - Change asset path (CDN usage)

**Settings storage:** WordPress Options API (`get_option('consentpro_*')`)

### Craft CMS Plugin

**File structure:**
- `src/ConsentPro.php` - Main module class
- `src/models/Settings.php` - Settings model (stored in project config)
- `src/services/ConsentService.php` - Config builder
- `src/controllers/SettingsController.php` - CP actions
- `src/twig/ConsentProExtension.php` - Twig functions

**Twig integration:**
```twig
{# Output banner manually #}
{{ craft.consentpro.banner() }}

{# Enable auto-inject in <head> #}
{% do craft.consentpro.autoInject() %}
```

**Key events:**
- `EVENT_BEFORE_RENDER` - Modify config before render
- `EVENT_REGISTER_CATEGORIES` - Add custom categories

**Settings storage:** Craft Project Config (supports multi-environment)

## Geo-Targeting Implementation

Relies on **Cloudflare `CF-IPCountry` header** (no external API calls):

**PHP logic** (both platforms):
```php
$geo = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null;
$eu_countries = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];

$region = null;
if ($geo === 'CA') $region = 'CA';
elseif (in_array($geo, $eu_countries)) $region = 'EU';

// Pass to frontend
return ['geo' => $region, 'geoEnabled' => $settings['geo_enabled']];
```

**Fallback:** If header missing and `geoEnabled=true`, default behavior is to show banner to all visitors (fail-safe for compliance).

## License Validation

**Tiers:** Core (free), Pro ($79/site), Enterprise ($299/site)

**Validation flow:**
1. Admin enters license key in settings
2. AJAX POST to remote API: `/validate` with `{key, domain, version}`
3. Response: `{valid: bool, tier: string, expires: date}`
4. Store encrypted in options/project config
5. Weekly cron/queue job re-validates (7-day grace period on API failure)

**Pro features gated by license:**
- Custom CSS injection field
- A/B testing (future)
- Analytics dashboard (future)

**Helper methods:**
- WordPress: `ConsentPro_License::is_pro()`
- Craft: `craft.consentpro.license.isPro()`

## Testing Strategy

### Unit Tests (Jest)
- **Target:** Core JS logic (ConsentManager, StorageAdapter, BannerUI)
- **Coverage:** ≥80% for all core files
- **Run:** `pnpm test` in `packages/consentpro-core/`

### Integration Tests (Cypress)
- **Scenarios:** Full consent flows, script blocking, geo-targeting, persistence
- **Run:** `pnpm cypress:run` (from monorepo root)

### Platform Tests
- **WordPress:** PHPUnit for settings, hooks, frontend output
- **Craft:** Codeception for CP settings, services, Twig output

### Manual QA Checklist
- Cross-browser: Chrome, Firefox, Safari, Edge (latest 2 versions)
- Mobile: iOS Safari, Android Chrome (real devices preferred)
- Accessibility: VoiceOver, NVDA, keyboard-only navigation
- Performance: Lighthouse score impact <5 points, no CLS

## Accessibility Requirements

**WCAG 2.1 AA compliance mandatory:**
- `role="dialog"` on banner container
- `aria-labelledby` pointing to heading ID
- `aria-modal="false"` (banner is non-blocking)
- Category toggles: `role="switch"`, `aria-checked` reflects state
- Focus trap: Tab cycles within banner when open, Escape closes without save
- Keyboard nav: Enter/Space activates buttons, focus returns to trigger on close
- Visual: 2px+ focus indicators, 4.5:1 text contrast, 3:1 UI contrast
- Screen readers: Live region announces "Preferences saved" on submit

## Performance Constraints

**Hard limits:**
- Combined JS + CSS bundle: <5KB gzipped
- Time to Interactive (TTI) impact: <50ms
- No Cumulative Layout Shift (CLS): banner space reserved on page load
- First Contentful Paint (FCP): not blocked by assets (use `defer`, `preload`)

**Optimization techniques:**
- Tree-shaking via Rollup (ES modules)
- No runtime dependencies (vanilla JS)
- Lazy-load Layer 2 DOM (only render on Settings click)
- SCSS compiled to minimal CSS, PostCSS minification

## Critical Technical Decisions

### Safari ITP Cookie Fallback
Safari's Intelligent Tracking Prevention limits localStorage lifespan. Use dual-write strategy:
```typescript
// StorageAdapter pattern
set(data): void {
  localStorage.setItem('consentpro_consent', JSON.stringify(data));  // Primary
  document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; max-age=31536000; path=/; SameSite=Lax`;  // Fallback
}

get(): ConsentData | null {
  return JSON.parse(localStorage.getItem('consentpro_consent'))
    ?? JSON.parse(decodeURIComponent(this.getCookie('consentpro')))
    ?? null;
}
```

### Config Hash for Re-Consent
Banner settings stored in `hash` field (SHA-256). If admin changes policy URL or category descriptions, hash mismatch forces re-consent prompt.

### Essential Category Immutability
`essential` category always `true` in code, toggle disabled in UI. Compliance requirement (GDPR Article 6.1f: legitimate interest for site functionality).

## Development Workflow

### Adding a New Consent Category

1. **Update TypeScript types** (`packages/consentpro-core/src/js/types.ts`):
   ```typescript
   interface ConsentCategories {
     essential: true;
     analytics: boolean;
     marketing: boolean;
     personalization: boolean;
     newCategory: boolean;  // Add here
   }
   ```

2. **Add to default categories** in ConsentManager constructor

3. **Update platform admin UI**:
   - WordPress: Add field in `admin/class-settings.php`, Categories tab
   - Craft: Add to `models/Settings.php`, update CP template

4. **Update SCSS** if category needs custom styling (`_settings-panel.scss`)

### Building for Production

```bash
# From monorepo root
pnpm install
pnpm build  # Builds core package

# WordPress plugin
cd plugins/consentpro-wp
composer install --no-dev --optimize-autoloader
# Copy dist/ from core to assets/

# Craft plugin
cd plugins/consentpro-craft
composer install --no-dev --optimize-autoloader
# Asset bundle references core dist/

# Verify bundle sizes
ls -lh packages/consentpro-core/dist/*.min.*
```

### Creating a Release

1. Update version in:
   - `packages/consentpro-core/package.json`
   - `plugins/consentpro-wp/consentpro.php` (plugin header)
   - `plugins/consentpro-craft/composer.json`

2. Build production assets (see above)

3. Tag release: `git tag -a v1.0.0 -m "Release 1.0.0"`

4. GitHub Actions workflow creates:
   - WordPress plugin zip (via `plugins/consentpro-wp/`)
   - Craft CMS plugin zip (via `plugins/consentpro-craft/`)
   - Uploads to GitHub Releases

## Common Gotchas

- **WordPress hook timing:** Banner injection uses `wp_footer` (priority 10). Scripts must be below banner in DOM for blocking to work. If theme doesn't call `wp_footer()`, banner won't appear.

- **Craft asset auto-inject:** If using `craft.consentpro.banner()` manually in template, disable auto-inject in settings to avoid duplicate banners.

- **Cookie fallback:** Cookie is first-party, same domain only. Won't work across subdomains unless `domain=.example.com` set (requires custom filter).

- **Geo header missing:** If not using Cloudflare, banner shows to all visitors by default (fail-safe). Can override via `consentpro_config` hook to manually detect region.

- **License validation failure:** 7-day grace period allows site to function during API downtime. After grace period, Pro features show upgrade prompt but Core features remain functional.

## Documentation References

- Technical Architecture: [docs/technical-architecture.md](docs/technical-architecture.md)
- Product Requirements: [docs/high-level-prd.md](docs/high-level-prd.md)
- Sprint Backlog: [docs/refined-backlog.md](docs/refined-backlog.md)
