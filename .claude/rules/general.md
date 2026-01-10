---
description: Global ConsentPro rules, DoD, monorepo, git
globs: ["**/*"]
enforce: true
---
# ConsentPro Global Rules

## Frontend UI: 
Use design skill for banners/admin—WCAG AA, no dark patterns, equal prominence, 44px touch, slide animations. Generate wireframes/HTML/CSS stubs before code.

## Definition of Done [file:4]
- Code reviewed/merged to main
- Unit tests ≥80% coverage touched files
- Integration tests pass
- Manual QA complete
- Deployed to staging
- Docs updated (README/inline)
- No critical/high bugs
- PO acceptance

## Monorepo [file:3]
packages/consentpro-core/  # TS/SCSS/Rollup
plugins/consentpro-wp/     # PHP/Settings API
plugins/consentpro-craft/  # PHP/CP/Twig

Root: pnpm-workspace.yaml, .github/workflows/ci.yml

## Git [file:4]
- Branches: feat/us-001-monorepo
- Commits: feat(fix/docs): message
- PRs: CI lint/test, <500 lines