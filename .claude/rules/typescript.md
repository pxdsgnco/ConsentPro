---
description: consentpro-core TS/JS rules Iteration 1
globs: ["packages/consentpro-core/src/**/*.ts", "packages/consentpro-core/src/**/*.js"]
---
# ConsentPro Core TS Rules [file:3][file:4]

## Classes
- ConsentManager: getConsent(): ConsentData|null, setConsent(cats), dispatch consentpro_consent
- StorageAdapter: dual localStorage/cookie, clear()
- GeoDetector: data-config → EU/CA/null, shouldShowBanner()

## Types
```ts
interface ConsentData {
  version: number;
  timestamp: number;
  geo: 'EU'|'CA'|null;
  categories: {essential: true, analytics?: boolean, marketing?: boolean, personalization?: boolean};
  hash: string;  // SHA256 config
}

## Build/Test [file:3]
- Rollup: dist/consentpro.min.js (IIFE), .min.css, .d.ts; <5KB gz
- Jest: 90% cov ConsentManager/Storage; Cypress E2E flows
