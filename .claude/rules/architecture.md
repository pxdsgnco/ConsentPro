---
description: Deep architecture - schemas, geo-targeting, license validation, technical decisions
globs: ["**/ConsentManager*", "**/StorageAdapter*", "**/GeoDetector*", "**/License*", "**/types.ts"]
---
# ConsentPro Deep Architecture

## Consent Storage Schema

```typescript
// localStorage key: 'consentpro_consent'
interface ConsentData {
  version: number;          // Schema version for migrations
  timestamp: number;        // Unix ms when consent given
  geo: 'EU' | 'CA' | null;
  categories: {
    essential: true;        // Always true, immutable
    analytics: boolean;
    marketing: boolean;
    personalization: boolean;
  };
  hash: string;             // SHA-256 of settings (config change → re-consent)
}
```

## Safari ITP Cookie Fallback

```typescript
// StorageAdapter dual-write pattern
set(data): void {
  localStorage.setItem('consentpro_consent', JSON.stringify(data));
  document.cookie = `consentpro=${encodeURIComponent(JSON.stringify(data))}; max-age=31536000; path=/; SameSite=Lax`;
}

get(): ConsentData | null {
  return JSON.parse(localStorage.getItem('consentpro_consent'))
    ?? JSON.parse(decodeURIComponent(this.getCookie('consentpro')))
    ?? null;
}
```

## Geo-Targeting (Cloudflare)

```php
$geo = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? null;
$eu = ['AT','BE','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IE','IT','LV','LT','LU','MT','NL','PL','PT','RO','SK','SI','ES','SE'];

$region = match(true) {
  $geo === 'CA' => 'CA',
  in_array($geo, $eu) => 'EU',
  default => null
};
```

**Fallback:** No header + `geoEnabled=true` → show to all (fail-safe compliance)

## License Validation

**Tiers:** Core (free) | Pro ($79/site) | Enterprise ($299/site)

**Flow:**
1. Admin enters key → AJAX POST `/validate` `{key, domain, version}`
2. Response: `{valid, tier, expires}`
3. Store encrypted in options/project config
4. Weekly cron re-validates (7-day grace on failure)

**Pro features:** Custom CSS, A/B testing (future), Analytics (future)

## Accessibility Requirements (WCAG 2.1 AA)

| Element | Requirement |
|---------|-------------|
| Banner container | `role="dialog"`, `aria-labelledby`, `aria-modal="false"` |
| Category toggles | `role="switch"`, `aria-checked` |
| Focus | Trap within banner, Escape closes, 2px+ indicators |
| Contrast | 4.5:1 text, 3:1 UI |
| Screen readers | Live region announces "Preferences saved" |

## Performance Constraints

| Metric | Limit |
|--------|-------|
| Bundle | <5KB gzipped |
| TTI impact | <50ms |
| CLS | None (reserve space) |
| FCP | Not blocked (`defer`, `preload`) |
