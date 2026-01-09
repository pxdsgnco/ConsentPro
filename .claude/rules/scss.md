---
description: SCSS banner styles Iteration 1
globs: ["packages/consentpro-core/scss/**/*.scss"]
---
# ConsentPro SCSS Rules [file:3]

## Structure
- variables.scss: $primary, $spacing-base, $bp-mobile: 768px
- banner.scss: Layer 1 fixed bottom
- settings-panel.scss: Layer 2 toggles
- animations.scss: slide-in 300ms ease-out

## Specs [file:4]
- Mobile-first responsive
- z-index: 999999
- Touch 44px, equal prominence buttons
- <2KB gzip, @media prefers-reduced-motion
