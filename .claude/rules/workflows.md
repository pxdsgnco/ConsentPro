---
description: Development workflows, release process, common gotchas
globs: ["**/package.json", "**/composer.json", "**/*.php", "**/CHANGELOG.md", "**/release.md"]
---
# ConsentPro Development Workflows

## Adding a New Consent Category

1. **Update types** (`packages/consentpro-core/src/js/types.ts`):
   ```typescript
   interface ConsentCategories {
     essential: true;
     analytics: boolean;
     newCategory: boolean;  // Add here
   }
   ```

2. **Add to defaults** in ConsentManager constructor

3. **Update admin UI:**
   - WP: `admin/class-settings.php` Categories tab
   - Craft: `models/Settings.php` + CP template

4. **Update SCSS** if custom styling needed

## Building for Production

```bash
# Full build
pnpm install && pnpm build

# WordPress plugin
cd plugins/consentpro-wp
composer install --no-dev --optimize-autoloader

# Craft plugin
cd plugins/consentpro-craft
composer install --no-dev --optimize-autoloader

# Verify sizes
ls -lh packages/consentpro-core/dist/*.min.*
```

## Creating a Release

1. **Update versions:**
   - `packages/consentpro-core/package.json`
   - `plugins/consentpro-wp/consentpro.php` (header)
   - `plugins/consentpro-craft/composer.json`

2. **Build production assets**

3. **Tag:** `git tag -a v1.0.0 -m "Release 1.0.0"`

4. GitHub Actions creates plugin zips

## Common Gotchas

| Issue | Cause | Solution |
|-------|-------|----------|
| Banner missing (WP) | Theme missing `wp_footer()` | Add to theme or use hook |
| Duplicate banners (Craft) | Manual + auto-inject | Disable auto-inject in settings |
| Cross-subdomain cookies | Cookie domain not set | Add `domain=.example.com` via filter |
| Geo header missing | Not using Cloudflare | Override via `consentpro_config` hook |
| License grace period | API downtime | 7-day grace, Core features remain |
