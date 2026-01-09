---
description: Craft plugin Iteration 3
globs: ["plugins/consentpro-craft/**/*.php"]
---
# ConsentPro Craft Rules [file:3][file:4]

## Scaffold
- ConsentPro.php: Craft5+/PHP8.2 CP nav

## Twig/Services
- {{ craft.consentpro.banner() }}
- autoInject() toggle
- ConsentService config, LicenseService::isPro()

## Events
- BEFORE_RENDER config mod
- Project config storage, queue validation