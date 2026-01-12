# CLAUDE.md

## Project Overview

**ConsentPro** - Cross-platform consent banner for WordPress/Craft CMS. Two-layer banner (quick actions + settings panel) with category-based script blocking.

**Monorepo:** `@consentpro/core` (TS/SCSS) + platform plugins (WP/Craft)

## Key Constraints

| Constraint | Limit |
|------------|-------|
| Bundle size | <5KB gzipped (JS+CSS) |
| Coverage | ≥80% unit tests |
| Accessibility | WCAG 2.1 AA |
| Compliance | GDPR/CCPA/ePrivacy |

## Quick Commands

```bash
# Core development
pnpm install && pnpm build       # Install & build
pnpm dev                         # Watch mode
pnpm test                        # Run tests
pnpm lint                        # Lint code

# Monorepo
pnpm -r build                    # Build all packages
pnpm --filter @consentpro/core test

# WordPress (plugins/consentpro-wp/)
composer test                    # PHPUnit tests
composer phpcs                   # Code standards

# Craft (plugins/consentpro-craft/)
composer test                    # Codeception tests

# E2E
pnpm cypress:run                 # All E2E tests
pnpm cypress:open                # Debug UI
```

## Architecture Quick Reference

```
@consentpro/core
├── ConsentManager    # State, storage, events → dispatch 'consentpro_consent'
├── StorageAdapter    # localStorage + cookie dual-write (Safari ITP)
├── BannerUI          # Layer 1 (banner) + Layer 2 (settings panel)
├── GeoDetector       # EU/CA from data-config (CF-IPCountry header)
└── ScriptBlocker     # type="text/plain" → text/javascript on consent
```

**Data flow:** Server injects `data-config` → Core checks storage → Show banner if no consent → User action → Store + fire event → Unblock scripts

**Script blocking pattern:**
```html
<script type="text/plain" data-consentpro="analytics" src="..."></script>
```

## Consent Categories

| Category | Default | Mutable |
|----------|---------|---------|
| essential | true | No (GDPR Art 6.1f) |
| analytics | false | Yes |
| marketing | false | Yes |
| personalization | false | Yes |

## Platform Hooks

**WordPress:**
- `consentpro_config` - Modify config before output
- `consentpro_categories` - Add/modify categories
- `consentpro_should_show` - Override visibility
- Check license: `ConsentPro_License::is_pro()`

**Craft CMS:**
- `{{ craft.consentpro.banner() }}` - Render banner
- `EVENT_BEFORE_RENDER` - Modify config
- Check license: `craft.consentpro.license.isPro()`

## Rules

@import .claude/rules/general.md
@import .claude/rules/typescript.md
@import .claude/rules/scss.md
@import .claude/rules/php-wp.md
@import .claude/rules/php-craft.md
@import .claude/rules/architecture.md
@import .claude/rules/debugging.md
@import .claude/rules/workflows.md

## Documentation

- [Technical Architecture](docs/technical-architecture.md)
- [Product Requirements](docs/high-level-prd.md)
- [Sprint Backlog](docs/refined-backlog.md)
