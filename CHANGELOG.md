# Changelog

All notable changes to ConsentPro will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-01-11

### Added

#### Core Package (`@consentpro/core`)

- **Consent Management**
  - `ConsentManager` class with `getConsent()`, `setConsent()`, and event dispatching
  - `StorageAdapter` with dual localStorage + cookie persistence (Safari ITP fallback)
  - `GeoDetector` module for EU/CA region detection from `data-config` attributes
  - `ScriptBlocker` for category-based script blocking/unblocking
  - Config hash generation for detecting settings changes (triggers re-consent)

- **Banner UI**
  - Layer 1: Quick actions banner with Accept All, Reject Non-Essential, Settings buttons
  - Layer 2: Settings panel with category toggles and Save Preferences
  - Footer privacy toggle for reopening banner after consent
  - Slide-in/out animations (300ms ease-out / 200ms ease-in)
  - `prefers-reduced-motion` media query support

- **Accessibility (WCAG 2.1 AA)**
  - `role="dialog"` with `aria-labelledby` and `aria-describedby`
  - Focus trap within banner when open
  - Keyboard navigation (Tab, Enter, Space, Escape)
  - `role="switch"` toggles with `aria-checked` state
  - Live region announcements for "Preferences saved"
  - 44px minimum touch targets
  - 4.5:1 text contrast ratio

- **Build & Testing**
  - Rollup build pipeline outputting IIFE bundle + CSS
  - Bundle size: <5KB gzipped (JS + CSS combined)
  - Jest unit tests for ConsentManager, StorageAdapter, GeoDetector, BannerUI, ScriptBlocker
  - 80%+ code coverage thresholds enforced
  - TypeScript definitions (`consentpro.d.ts`)

#### WordPress Plugin (`consentpro-wp`)

- **Admin Settings** (5-tab interface)
  - General: Privacy Policy URL, geo-targeting toggle, banner enabled
  - Appearance: Color pickers (primary, secondary, text, background) + text fields
  - Categories: Name and description for each consent category
  - Consent Log: Dashboard metrics (30-day totals, percentages) + event table
  - License: Key input, activation status, tier display

- **Frontend Integration**
  - Banner injection via `wp_footer` hook
  - `data-config` JSON attribute with all settings
  - Assets enqueued with version hash, defer loading

- **Hooks & Filters**
  - `consentpro_config` - Modify config array before output
  - `consentpro_categories` - Add/modify consent categories
  - `consentpro_should_show` - Override banner visibility
  - `consentpro_assets_url` - Change asset path (CDN support)

- **Geo-Targeting**
  - Cloudflare `CF-IPCountry` header detection
  - EU (27 countries) and CA region support
  - Configurable fallback behavior

- **License Validation**
  - Remote API validation with domain verification
  - Weekly cron re-validation
  - 7-day grace period on API failure
  - Pro feature gating (Custom CSS field)

#### Craft CMS Plugin (`consentpro-craft`)

- **Control Panel Settings**
  - Tabbed interface matching Craft CP patterns
  - Live preview panel with mobile toggle
  - Project config storage (multi-environment support)

- **Twig Integration**
  - `{{ craft.consentpro.banner() }}` - Manual banner output
  - `{{ craft.consentpro.scripts() }}` - Asset output
  - `{% do craft.consentpro.autoInject() %}` - Auto-inject mode

- **Services**
  - `ConsentService` for config building
  - `LicenseService` with `isPro()`, `isEnterprise()` helpers
  - `ConsentLogService` for metrics aggregation

- **Events**
  - `EVENT_BEFORE_RENDER` - Modify config before render
  - `EVENT_REGISTER_CATEGORIES` - Add custom categories

- **Queue Jobs**
  - Weekly license re-validation via Craft Queue
  - Daily consent log aggregation
  - Auto-prune events older than 90 days

#### Testing & CI

- **Cypress E2E Tests**
  - Consent flows (Accept All, Reject, Custom Save)
  - Script blocking and unblocking
  - Safari ITP cookie fallback verification
  - Mobile responsive layout (iPhone SE, iPhone 14, Pixel 5)
  - Keyboard navigation audit
  - ARIA markup audit
  - Performance benchmarks

- **Platform Tests**
  - WordPress: PHPUnit tests for Settings API, hooks, sanitization
  - Craft: Codeception tests for services, models, Twig extension

- **GitHub Actions CI**
  - ESLint + Prettier for JS/TS
  - Stylelint for SCSS
  - PHPCS for WordPress (WPCS standard)
  - PHP syntax check for Craft
  - Bundle size verification (<5KB limit)
  - Jest test coverage upload to Codecov

#### Documentation

- Comprehensive README with quick start guide
- Integration examples for both platforms
- Script blocking pattern documentation
- Configuration reference table
- Accessibility requirements
- Browser support matrix (Chrome 80+, Firefox 75+, Safari 13+, Edge 80+)

### Security

- XSS prevention via `esc_html()` / `wp_kses()` (WordPress)
- CSRF protection on all admin forms
- Encrypted license key storage
- No PII stored in consent logs (anonymized counts only)
- `SameSite=Lax` cookie attribute for CSRF mitigation

### Performance

- <5KB gzipped bundle (JS + CSS)
- No external API calls for geo-detection (header-based)
- Lazy-load Layer 2 DOM (only rendered on Settings click)
- CSS-only animations (GPU-accelerated transforms)
- No Cumulative Layout Shift (fixed positioning)
- `defer` attribute on script loading

## [Unreleased]

### Planned

- A/B testing for banner variants (Pro)
- Analytics dashboard with conversion metrics (Pro)
- Additional consent categories (functional, social)
- IAB TCF 2.0 compatibility mode
- Google Consent Mode v2 integration

---

[1.0.0]: https://github.com/pxdsgnco/ConsentPro/releases/tag/v1.0.0
[Unreleased]: https://github.com/pxdsgnco/ConsentPro/compare/v1.0.0...HEAD
