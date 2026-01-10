# Refined ConsentPro Backlog (Single-Iteration Epics)

## Global Definition of Done (All Stories)

Every story must satisfy these criteria before closure:

- [ ] Code reviewed and merged to `main` branch
- [ ] Unit tests pass (≥80% coverage for touched files)
- [ ] Integration tests pass (where applicable)
- [ ] Manual QA checklist complete
- [ ] Deployed to staging environment
- [ ] Documentation updated (README, inline docs)
- [ ] No critical/high bugs open
- [ ] Product Owner acceptance

-----

## Iteration 1: Core Foundation + Banner UI (E1 + E2) ✅ COMPLETE

**Status:** All 18 stories completed and merged to master.

**Goal:** Standalone HTML demo with full Layer 1/2 consent flow, no platform integration.

**Shippable Increment:** `consentpro-core` package builds to <5KB, demo.html shows working banner with localStorage persistence.

|ID     |Title                              |Acceptance Criteria                                                                                                                                                                                                                                                     |Est|Story-Specific DoD                   |
|-------|-----------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|-------------------------------------|
|US-001 |Monorepo setup with pnpm workspaces|- `pnpm install` resolves all workspaces<br>- packages/consentpro-core structure created<br>- plugins/consentpro-wp and plugins/consentpro-craft scaffolded (empty)<br>- .gitignore, .eslintrc, .prettierrc configured<br>- CI workflow stub (.github/workflows/ci.yml) |2  |CI pipeline runs lint on PR          |
|US-002 |Rollup build pipeline              |- `pnpm build` outputs dist/consentpro.min.js (IIFE)<br>- dist/consentpro.min.css generated<br>- dist/consentpro.d.ts types exported<br>- Source maps generated<br>- Gzipped JS+CSS combined <5KB                                                                       |2  |Build runs in CI, artifacts uploaded |
|US-003 |ConsentManager class               |- `getConsent()` returns ConsentData or null<br>- `setConsent(categories)` persists with timestamp, version, hash<br>- `consentpro_consent` CustomEvent fires on document<br>- Essential always true, immutable<br>- Schema: {version, timestamp, geo, categories, hash}|3  |Jest tests ≥90% coverage             |
|US-004 |StorageAdapter with cookie fallback|- Writes to localStorage + cookie simultaneously<br>- Reads localStorage first, cookie fallback<br>- Cookie: max-age=31536000, SameSite=Lax<br>- Handles invalid JSON gracefully (returns null)<br>- Clear method removes both storage types                            |3  |Jest tests mock localStorage blocking|
|US-005 |GeoDetector module                 |- Reads geo from `data-config` JSON attribute<br>- Returns “EU”, “CA”, or null<br>- No external API calls<br>- Exposes `shouldShowBanner(config)` helper                                                                                                                |1  |Unit tests for all regions           |
|US-006a|Layer 1 banner DOM structure       |- Renders container with heading, 3 buttons<br>- Semantic HTML: `<aside role="dialog">`<br>- Buttons: Accept All, Reject Non-Essential, Settings<br>- Fixed position bottom, full-width mobile<br>- Max-width 960px centered on desktop<br>- z-index: 999999            |2  |HTML validates (W3C)                 |
|US-006b|Layer 1 banner styling             |- SCSS variables for colors, spacing<br>- Mobile-first responsive (768px breakpoint)<br>- Equal button prominence (no dark patterns)<br>- Touch targets ≥44px<br>- CSS <2KB gzipped                                                                                     |2  |Visual regression baseline captured  |
|US-006c|Layer 1 banner animations          |- Slide-in from bottom on show (300ms ease-out)<br>- Slide-out on hide (200ms ease-in)<br>- CSS transitions only (no JS animation library)<br>- Respects `prefers-reduced-motion`                                                                                       |1  |Animation renders at 60fps           |
|US-007a|Layer 2 panel DOM structure        |- 4 category toggles (Essential disabled)<br>- Category name + description for each<br>- Privacy Policy link (opens new tab)<br>- Back link, Save Preferences button<br>- Replaces Layer 1 (same container)                                                             |3  |HTML validates (W3C)                 |
|US-007b|Layer 2 toggle interactions        |- Toggle switches use `<button role="switch">`<br>- `aria-checked` updates on click<br>- Essential toggle visually disabled, always checked<br>- State tracked in memory before save<br>- Visual feedback on toggle (color change)                                      |2  |Keyboard toggle works (Space/Enter)  |
|US-008 |Accept All action                  |- Click sets all 4 categories to true<br>- Calls ConsentManager.setConsent()<br>- Banner closes with slide-out<br>- Event fires with full consent<br>- Works on Enter keypress                                                                                          |1  |E2E test: click → storage verified   |
|US-009 |Reject Non-Essential action        |- Click sets Essential=true, others=false<br>- Calls ConsentManager.setConsent()<br>- Banner closes with slide-out<br>- Event fires with minimal consent<br>- No confirmation dialog                                                                                    |1  |E2E test: click → storage verified   |
|US-010 |Save Preferences action            |- Click reads toggle states<br>- Calls ConsentManager.setConsent() with selections<br>- Banner closes<br>- Event fires with custom consent<br>- Essential always true regardless of UI                                                                                  |2  |E2E test: custom selection persists  |
|US-011 |Consent persistence check          |- On init, check storage for valid consent<br>- If valid and <12 months old, hide banner<br>- If config hash changed, show banner (re-consent)<br>- Check completes <50ms<br>- No layout shift (banner space reserved)                                                  |2  |Performance test: init time logged   |
|US-015 |SCSS architecture                  |- _variables.scss: colors, spacing, breakpoints, z-index<br>- _banner.scss: Layer 1 styles<br>- _settings-panel.scss: Layer 2 styles<br>- _animations.scss: transitions<br>- main.scss imports all partials<br>- No `!important` except overrides                       |2  |Stylelint passes, no errors          |
|US-013 |Footer privacy toggle              |- Renders `<button>` with configurable text<br>- Click reopens Layer 1 banner<br>- aria-label describes action<br>- Visually minimal (text link style)<br>- Only visible after consent given                                                                            |2  |Toggle cycles banner open/close      |
|US-014a|Keyboard accessibility             |- Tab cycles through all interactive elements<br>- Focus trapped in banner when open<br>- Escape closes banner (no save)<br>- Focus returns to trigger on close<br>- Visible focus indicators (2px outline)                                                             |2  |Manual QA with keyboard-only         |
|US-014b|Screen reader accessibility        |- `role="dialog"`, `aria-labelledby` on container<br>- `aria-modal="false"` (non-blocking)<br>- Live region announces “Preferences saved”<br>- Toggles announce state changes<br>- Color contrast ≥4.5:1 (text), ≥3:1 (UI)                                              |2  |VoiceOver + NVDA tested              |

**Iteration 1 Total: 18 stories, 35 points**

-----

## Iteration 2: Consent Logic + WordPress MVP (E3 + E4) ✅ COMPLETE

**Status:** All 14 stories completed and merged to master.

**Goal:** Working WordPress plugin with admin settings and frontend banner.

**Shippable Increment:** WP plugin activates, admin can configure banner, visitors see geo-targeted consent prompt.

|ID     |Title                          |Acceptance Criteria                                                                                                                                                                                                                                                         |Est|Story-Specific DoD                  |
|-------|-------------------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|------------------------------------|
|US-012a|Script blocking on page load   |- Scripts with `type="text/plain" data-consentpro="category"` stay inert<br>- On init, if category consented, change type to “text/javascript”<br>- Script executes after type change<br>- Essential scripts always execute<br>- Works for external `src` and inline scripts|3  |Test with GA4, Meta Pixel scripts   |
|US-012b|Dynamic script unblocking      |- On `consentpro_consent` event, scan for blocked scripts<br>- Unblock matching category scripts<br>- Scripts execute in DOM order<br>- Already-executed scripts don’t re-run<br>- MutationObserver catches dynamically added scripts                                       |3  |Cypress test: consent → script fires|
|US-016 |WP plugin scaffold             |- Plugin header with Name, Version, Author, License<br>- Requires WP 6.0+, PHP 7.4+<br>- Activation creates default options<br>- Menu item under Settings → ConsentPro<br>- Deactivation preserves settings<br>- Uninstall removes all options                              |2  |Plugin activates without errors     |
|US-017 |WP General settings tab        |- Fields: Privacy Policy URL, geo toggle, banner enabled<br>- URL validates or allows empty<br>- Settings API registration<br>- Sanitization on save<br>- Success notice on save<br>- Settings escaped on output                                                            |2  |Settings round-trip test            |
|US-018a|WP Appearance tab - colors     |- 4 color pickers: primary, secondary, text, background<br>- Uses wp-color-picker<br>- Defaults: blue primary, white bg, dark text<br>- Hex validation on save<br>- Colors output as CSS variables                                                                          |2  |Color picker functional             |
|US-018b|WP Appearance tab - text fields|- Fields: heading, accept btn, reject btn, settings link, save btn<br>- Character limits enforced (100/30)<br>- Defaults provided<br>- XSS sanitization (esc_html)<br>- Placeholder text shown                                                                              |2  |Text persists through save          |
|US-019 |WP Categories settings tab     |- 4 category sections with name + description fields<br>- Essential description notes “always enabled”<br>- Default descriptions explain purpose<br>- Basic HTML allowed in descriptions (links only)<br>- wp_kses sanitization                                             |2  |Categories save/load correctly      |
|US-020a|WP Consent log - basic metrics |- Dashboard widget shows: total consents (30d), accept %, reject %, custom %<br>- Data from client-side localStorage aggregation<br>- AJAX endpoint returns anonymized counts<br>- No PII stored<br>- Empty state message                                                   |3  |Metrics display correctly           |
|US-020b|WP Consent log - event table   |- Table shows last 50 events: timestamp, categories, region<br>- Pagination (50 per page)<br>- “Clear Log” button with confirmation<br>- Log stored in custom table (wp_consentpro_log)<br>- Auto-prune events >90 days                                                     |3  |Table renders, clear works          |
|US-021a|WP Admin preview - basic       |- Preview iframe on settings page (right side desktop)<br>- Shows Layer 1 banner with current settings<br>- Toggle button switches to Layer 2<br>- Isolated styles (iframe sandbox)                                                                                         |3  |Preview renders correctly           |
|US-021b|WP Admin preview - live updates|- Preview updates on field change (debounced 300ms)<br>- Color changes reflect immediately<br>- Text changes reflect immediately<br>- Mobile view toggle button<br>- Preview maintains state during edits                                                                   |2  |Live update functional              |
|US-022 |WP Frontend banner injection   |- Banner HTML output in wp_footer<br>- data-config JSON includes all settings<br>- JS/CSS enqueued with version hash<br>- Scripts defer, styles preload<br>- Auto-init on DOMContentLoaded                                                                                  |2  |Banner appears on frontend          |
|US-023 |WP Filter hooks                |- `consentpro_config` modifies config array<br>- `consentpro_categories` modifies categories<br>- `consentpro_should_show` returns bool<br>- `consentpro_assets_url` overrides asset path<br>- Hooks documented with examples                                               |2  |Each hook tested with sample code   |
|US-026 |WP Geo-targeting implementation|- Reads CF-IPCountry header server-side<br>- EU countries list (27 members)<br>- CA detected separately<br>- If geo enabled + non-EU/CA, banner hidden<br>- If header missing, fallback to config (default: show all)<br>- Region passed in data-config                     |3  |Mock headers test all regions       |

**Iteration 2 Total: 14 stories, 34 points**

-----

## Iteration 3: Craft CMS Port + Licensing (E5 + E6)

**Goal:** Working Craft plugin with CP settings, plus license validation for both platforms.

**Shippable Increment:** Craft plugin installs via Composer, CP settings work, license gating functional on both platforms.

|ID     |Title                      |Acceptance Criteria                                                                                                                                                                                                                         |Est|Story-Specific DoD               |
|-------|---------------------------|--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|---------------------------------|
|US-027 |Craft plugin scaffold      |- composer.json with proper namespacing<br>- Requires Craft 5.x, PHP 8.2+<br>- `craft plugin/install consentpro` works<br>- CP nav item under Settings<br>- Uninstall removes project config entries                                        |2  |Plugin installs without errors   |
|US-028 |Craft General settings     |- Fields: Privacy Policy URL, geo toggle, banner enabled<br>- Stored in project config<br>- Environment variable overrides supported<br>- Validation with flash messages<br>- Multi-environment ready                                       |2  |Settings sync in project config  |
|US-029 |Craft Appearance settings  |- Color fields using Craft color input<br>- Text fields with char limits<br>- Live preview panel (updates on blur)<br>- Defaults populated<br>- Project config storage                                                                      |2  |Appearance saves correctly       |
|US-030 |Craft Categories settings  |- 4 category fieldsets: name + description<br>- Essential toggle always disabled/on<br>- Stored as array in project config<br>- Default descriptions on install                                                                             |2  |Categories persist correctly     |
|US-031a|Craft Consent log - metrics|- Utilities → Consent Log displays dashboard<br>- Same metrics as WP: totals, percentages<br>- Controller action returns JSON<br>- Admin-only permission                                                                                    |2  |Metrics display in CP            |
|US-031b|Craft Consent log - storage|- Custom database table via migration<br>- Queue job aggregates daily stats<br>- Clear action with confirmation<br>- Auto-prune >90 days                                                                                                    |3  |Log table created, prune works   |
|US-032 |Craft Twig extension       |- `{{ craft.consentpro.banner() }}` outputs HTML<br>- `{{ craft.consentpro.scripts() }}` outputs assets<br>- `{% do craft.consentpro.autoInject() %}` enables auto-inject<br>- Returns safe HTML (Twig_Markup)<br>- Empty output if disabled|2  |Twig functions render correctly  |
|US-033 |Craft Asset bundle         |- AssetBundle registers JS/CSS<br>- Asset versioning with file hash<br>- Footer loading with defer<br>- Auto-inject setting controls registration<br>- Works with Craft asset pipeline                                                      |2  |Assets load on frontend          |
|US-036 |Craft events/hooks         |- EVENT_BEFORE_RENDER for config modification<br>- EVENT_REGISTER_CATEGORIES for custom categories<br>- Standard Craft Event pattern<br>- Documented in README                                                                              |2  |Events fire and modify output    |
|US-024a|WP License tab UI          |- License key input field<br>- Activate/Deactivate buttons<br>- Status display: Active/Inactive, tier, expiry<br>- Error messages for invalid keys<br>- Loading state during validation                                                     |2  |UI renders all states            |
|US-024b|WP License validation      |- AJAX call to remote API: POST /validate<br>- Payload: key, domain, plugin version<br>- Response: valid, tier, expires<br>- Store encrypted in wp_options<br>- 7-day grace period on API failure                                           |3  |Validation succeeds with test key|
|US-024c|WP License cron            |- Weekly cron re-validates license<br>- Uses wp_schedule_event<br>- Updates cached status<br>- Logs validation failures<br>- Grace period countdown                                                                                         |2  |Cron runs, updates status        |
|US-025 |WP Pro feature gating      |- Custom CSS field in Appearance tab<br>- Shows upgrade prompt if no valid Pro license<br>- Editable textarea if Pro+<br>- CSS injected inline after main stylesheet<br>- `is_pro()` helper function                                        |2  |Gating works based on license    |
|US-034a|Craft License settings UI  |- License section in CP settings<br>- Key input, activate button<br>- Status display with tier/expiry<br>- Styled consistently with Craft CP                                                                                                |2  |UI matches Craft patterns        |
|US-034b|Craft License service      |- LicenseService with validate() method<br>- Same API as WP validation<br>- Store in project config (encrypted)<br>- `isPro()`, `isEnterprise()` helpers                                                                                    |3  |Service validates correctly      |
|US-034c|Craft License queue job    |- Weekly validation via Craft Queue<br>- RegisterQueueJobEvent on module init<br>- Updates cached status<br>- Handles API failures gracefully                                                                                               |2  |Queue job executes               |
|US-035 |Craft Pro feature gating   |- Custom CSS field gated by license<br>- Upgrade prompt for unlicensed<br>- CSS output in asset bundle<br>- Uses LicenseService::isPro()                                                                                                    |2  |Gating matches WP behavior       |

**Iteration 3 Total: 17 stories, 37 points**

-----

## Iteration 4: Compliance, QA & Release (E7)

**Goal:** Production-ready release with full cross-browser support, accessibility compliance, and documentation.

**Shippable Increment:** Both plugins pass all automated tests, accessibility audit, performance benchmarks. Ready for marketplace submission.

|ID     |Title                        |Acceptance Criteria                                                                                                                                                                                                                    |Est|Story-Specific DoD                |
|-------|-----------------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|---|----------------------------------|
|US-037a|Mobile responsive - layout   |- Full-width banner on viewport <768px<br>- Stacked buttons (vertical)<br>- Layer 2 scrolls if content exceeds viewport<br>- No horizontal scroll ever<br>- Test: iPhone SE, iPhone 14, Pixel 5                                        |2  |Screenshots captured all devices  |
|US-037b|Mobile responsive - touch    |- Touch targets ≥44x44px<br>- No hover-dependent interactions<br>- Swipe-to-dismiss optional enhancement<br>- Touch feedback (active states)<br>- Test on real devices (not just emulator)                                             |2  |Touch interactions verified       |
|US-038 |Keyboard navigation audit    |- Tab order logical (left→right, top→bottom)<br>- All interactive elements focusable<br>- Enter/Space activate buttons/toggles<br>- Escape closes without save<br>- Focus visible (2px+ outline)<br>- Focus returns to trigger on close|2  |Keyboard-only test complete       |
|US-039a|ARIA markup audit            |- `role="dialog"` on container<br>- `aria-labelledby` points to heading ID<br>- `aria-modal="false"` (non-blocking)<br>- Toggles: `role="switch"`, `aria-checked`<br>- Buttons: meaningful labels                                      |2  |aXe DevTools scan passes          |
|US-039b|Screen reader testing        |- VoiceOver (macOS): full flow tested<br>- VoiceOver (iOS): full flow tested<br>- NVDA (Windows): full flow tested<br>- Live region announces save confirmation<br>- No unlabeled elements                                             |3  |Recorded screen reader demos      |
|US-040a|Lighthouse performance audit |- Performance score impact <5 points<br>- No CLS from banner appearance<br>- FCP not blocked by assets<br>- Audit on mobile + desktop<br>- Document baseline vs with-plugin scores                                                     |2  |Lighthouse reports attached       |
|US-040b|Performance optimization     |- Total blocking time contribution <50ms<br>- Assets use defer + preload hints<br>- CSS critical path optimized<br>- Lazy-load Layer 2 DOM (only render on Settings click)<br>- Bundle size verified <5KB gzip                         |2  |Performance budget met            |
|US-041a|Chrome/Edge testing          |- Chrome 90+ (Windows): full flow<br>- Chrome 90+ (macOS): full flow<br>- Edge 90+ (Windows): full flow<br>- No console errors<br>- Visual appearance matches designs                                                                  |2  |Browser matrix complete           |
|US-041b|Firefox testing              |- Firefox 90+ (Windows): full flow<br>- Firefox 90+ (macOS): full flow<br>- CSS renders correctly<br>- No console errors<br>- Form controls styled correctly                                                                           |1  |Firefox tests pass                |
|US-041c|Safari testing               |- Safari 14+ (macOS): full flow<br>- Safari (iOS 14+): full flow<br>- ITP cookie fallback verified<br>- No console errors<br>- CSS renders correctly                                                                                   |2  |Safari ITP specifically tested    |
|US-042a|Core JS unit tests           |- ConsentManager: 90%+ coverage<br>- StorageAdapter: 90%+ coverage<br>- GeoDetector: 100% coverage<br>- BannerUI: 80%+ coverage<br>- Jest test suite passes                                                                            |3  |Coverage report attached          |
|US-042b|Core JS test CI integration  |- Tests run on every PR<br>- Coverage threshold enforced (80%)<br>- Test results posted to PR<br>- Failing tests block merge                                                                                                           |1  |CI workflow configured            |
|US-043a|Cypress E2E - consent flows  |- Test: Layer 1 → Accept All → verify storage<br>- Test: Layer 1 → Reject → verify storage<br>- Test: Layer 1 → Layer 2 → custom save → verify<br>- Test: persistence across reload<br>- Test: footer toggle reopens banner            |3  |All E2E tests pass                |
|US-043b|Cypress E2E - script blocking|- Test: blocked script with data-consentpro stays inert<br>- Test: consent given → script executes<br>- Test: category-specific blocking works<br>- Test: essential scripts always run                                                 |2  |Script blocking E2E passes        |
|US-043c|Cypress E2E - geo targeting  |- Test: mock EU header → banner shows<br>- Test: mock CA header → banner shows<br>- Test: mock US header → banner hidden (geo enabled)<br>- Test: geo disabled → banner shows all                                                      |2  |Geo tests pass with mocked headers|
|US-044 |WP integration tests         |- PHPUnit: Settings API save/load<br>- PHPUnit: Option sanitization<br>- PHPUnit: Hook filters execute<br>- WP test suite: activation, deactivation, uninstall<br>- Test banner output in wp_footer                                    |3  |WP tests pass in CI               |
|US-045 |Craft integration tests      |- Codeception: Settings model validation<br>- Codeception: ConsentService output<br>- Codeception: LicenseService validation<br>- Test Twig extension output<br>- Test project config sync                                             |3  |Craft tests pass in CI            |
|US-046a|Plugin README documentation  |- Installation instructions (both platforms)<br>- Quick start (5-minute setup)<br>- Settings reference (all options)<br>- Hook/event reference with examples<br>- Troubleshooting section                                              |2  |README complete, reviewed         |
|US-046b|Inline code documentation    |- PHPDoc for all public PHP methods<br>- JSDoc for all public JS methods<br>- TypeScript interfaces documented<br>- Code comments for complex logic                                                                                    |2  |Docs generate without errors      |
|US-046c|CHANGELOG setup              |- CHANGELOG.md following Keep a Changelog<br>- Version 1.0.0 entry complete<br>- Categories: Added, Changed, Fixed<br>- Links to issues/PRs where applicable                                                                           |1  |CHANGELOG formatted correctly     |
|US-047 |No-JS graceful degradation   |- Banner does not render if JS disabled<br>- Page content fully accessible<br>- Optional `<noscript>` static notice<br>- Scripts with data-consentpro stay inert (no errors)<br>- No console errors or failed requests                 |1  |No-JS test passes                 |

**Iteration 4 Total: 21 stories, 43 points** ⚠️ *3 points over capacity*

-----

## Story Point Capacity Check

|Iteration|Epics    |Stories|Points |Capacity (40)|Status              |
|---------|---------|-------|-------|-------------|---------------------|
|1        |E1 + E2  |18     |35     |40           |✅ **COMPLETE**      |
|2        |E3 + E4  |14     |34     |40           |✅ **COMPLETE**      |
|3        |E5 + E6  |17     |37     |40           |🔲 Pending           |
|4        |E7       |21     |43     |40           |🔲 Pending           |
|**Total**|**E1-E7**|**70** |**149**|**160**      |**32/70 stories done**|

### Iteration 4 Mitigation Options

**Option A (Recommended):** Move US-046c (CHANGELOG, 1pt) to post-release task. Move US-041b (Firefox, 1pt) to parallel with US-041a. **Adjusted: 41pts → acceptable with pair work.**

**Option B:** Defer US-043c (geo E2E, 2pts) since geo logic tested in US-026. **Adjusted: 41pts.**

**Option C:** Combine US-039a + US-039b into single “Accessibility audit” (4pts instead of 5). **Adjusted: 42pts → still over.**

**Recommendation:** Apply Option A. Firefox testing can parallel Chrome/Edge on day 1 of QA. CHANGELOG is documentation debt, acceptable for v1.0.1.

-----

## Changes Made from Original Backlog

### Stories Split

|Original    |Split Into                           |Reason                                             |
|------------|-------------------------------------|---------------------------------------------------|
|US-006 (5pt)|US-006a (2), US-006b (2), US-006c (1)|Separate DOM, styling, animation concerns          |
|US-007 (5pt)|US-007a (3), US-007b (2)             |Separate structure from interactions               |
|US-012 (5pt)|US-012a (3), US-012b (3)             |“Block on load” vs “dynamic unblock” are distinct  |
|US-014 (5pt)|US-014a (2), US-014b (2)             |Keyboard vs screen reader require different testing|
|US-018 (5pt)|US-018a (2), US-018b (2)             |Colors vs text fields can parallelize              |
|US-020 (5pt)|US-020a (3), US-020b (3)             |Basic metrics vs detailed table are separable      |
|US-021 (5pt)|US-021a (3), US-021b (2)             |Basic preview vs live updates                      |
|US-024 (5pt)|US-024a (2), US-024b (3), US-024c (2)|UI, validation, cron are distinct                  |
|US-031 (5pt)|US-031a (2), US-031b (3)             |Metrics vs storage/prune logic                     |
|US-034 (5pt)|US-034a (2), US-034b (3), US-034c (2)|UI, service, queue job                             |
|US-037 (3pt)|US-037a (2), US-037b (2)             |Layout vs touch interactions                       |
|US-039 (5pt)|US-039a (2), US-039b (3)             |ARIA markup vs screen reader testing               |
|US-040 (3pt)|US-040a (2), US-040b (2)             |Audit vs optimization                              |
|US-041 (5pt)|US-041a (2), US-041b (1), US-041c (2)|Chrome/Edge, Firefox, Safari separate              |
|US-042 (5pt)|US-042a (3), US-042b (1)             |Tests vs CI integration                            |
|US-043 (5pt)|US-043a (3), US-043b (2), US-043c (2)|Consent flows, script blocking, geo                |
|US-046 (3pt)|US-046a (2), US-046b (2), US-046c (1)|README, inline docs, changelog                     |

### Stories Removed/Deferred

|ID        |Title            |Reason          |Moved To            |
|----------|-----------------|----------------|--------------------|
|US-048-057|Post-MVP features|Not in MVP scope|Nice-to-Have backlog|

### Stories Reprioritized

|ID       |Original Iteration|New Iteration|Reason                                                          |
|---------|------------------|-------------|----------------------------------------------------------------|
|US-012a/b|Week 1            |Iteration 2  |Script blocking depends on WP integration for real-world testing|
|US-013   |Week 1            |Iteration 1  |Footer toggle is core UX, needed for demo                       |
|US-014a/b|Week 1            |Iteration 1  |Accessibility is foundational, not polish                       |

### Estimates Adjusted

|ID     |Original|New|Reason                                    |
|-------|--------|---|------------------------------------------|
|US-003 |3       |3  |Kept, but added hash generation complexity|
|US-024b|-       |3  |Remote API integration more complex       |
|US-020b|-       |3  |Database table + pagination adds scope    |

-----

## Dependency Graph (Critical Path)

```
Iteration 1: ✅ COMPLETE
US-001 (monorepo) ✓
  └─→ US-002 (build) ✓
       └─→ US-003 (ConsentManager) + US-004 (Storage) + US-005 (Geo) ✓
            └─→ US-006a/b/c (Layer 1) + US-015 (SCSS) ✓
                 └─→ US-007a/b (Layer 2) ✓
                      └─→ US-008, US-009, US-010, US-011 (actions) ✓
                           └─→ US-013, US-014a/b (toggle, a11y) ✓

Iteration 2: ✅ COMPLETE
[Iteration 1 complete] ✓
  └─→ US-012a/b (script blocking) ✓
       └─→ US-016 (WP scaffold) ✓
            └─→ US-017 (general) + US-018a/b (appearance) + US-019 (categories) ✓
                 └─→ US-020a/b (consent log) + US-021a/b (preview) ✓
                      └─→ US-022 (frontend) + US-023 (hooks) + US-026 (geo) ✓

Iteration 3: 🔲 NEXT
[Iteration 2 complete] ✓
  └─→ US-027 (Craft scaffold)
       └─→ US-028, US-029, US-030, US-031a/b, US-032, US-033, US-036 (Craft features)
  └─→ US-024a/b/c (WP license) ─→ US-025 (WP gating)
  └─→ US-034a/b/c (Craft license) ─→ US-035 (Craft gating)

Iteration 4: 🔲 PENDING
[Iteration 3 complete]
  └─→ All US-037-047 can parallelize across team members
       └─→ Final integration testing
            └─→ Documentation + Release prep
```

-----

## Risk Register (Updated)

|Risk                           |Likelihood|Impact|Mitigation                                                                    |Iteration|
|-------------------------------|----------|------|------------------------------------------------------------------------------|---------|
|Iteration 4 over capacity      |High      |Medium|Apply Option A: defer CHANGELOG, parallelize Firefox                          |4        |
|License API not ready          |Medium    |High  |Build mock API for testing; 7-day grace period                                |3        |
|Safari ITP regression          |Low       |Medium|Cookie fallback tested in Iteration 1; retest in Iteration 4                  |1, 4     |
|Screen reader testing resources|Medium    |Medium|Partner with accessibility consultant or use automated tools (aXe) as baseline|4        |
|Craft 5.x breaking changes     |Low       |Medium|Pin to Craft 5.0.0 stable; test against 5.1 RC                                |3        |

-----

## Summary

|Metric            |Original  |Refined    |Current Status           |
|------------------|----------|-----------|-------------------------|
|Total Stories     |47        |70         |**32/70 complete (46%)** |
|Total Points      |169       |149        |**69/149 delivered (46%)**|
|Iterations Done   |-         |4          |**2/4 complete**         |
|Max Story Size    |5         |3          |All stories ≤3pt         |
|Iteration Variance|Unbalanced|35/34/37/43|Even distribution        |

### Progress Summary

- ✅ **Iteration 1** (Core Foundation + Banner UI): 18 stories, 35 points — **COMPLETE**
- ✅ **Iteration 2** (WordPress MVP): 14 stories, 34 points — **COMPLETE**
- 🔲 **Iteration 3** (Craft CMS + Licensing): 17 stories, 37 points — **NEXT**
- 🔲 **Iteration 4** (Compliance, QA & Release): 21 stories, 43 points — **PENDING**

**Next milestone:** Iteration 3 — Craft CMS Port + Licensing (E5 + E6)