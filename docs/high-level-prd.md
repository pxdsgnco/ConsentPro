**ConsentPro: Premium Single-Layer Consent Banner Plugin** is a cross-platform (WordPress & Craft CMS) solution delivering a compliant, high-conversion two-layer consent banner that combines privacy policy acknowledgment, cookie consent, and policy links into one frictionless experience, minimizing drop-offs per UX best practices.

## Product Overview
Designed for sites needing GDPR/CCPA/ePrivacy compliance, ConsentPro replaces multiple nagging dialogs with a single, customizable banner. Layer 1 offers quick Accept/Reject/Settings choices (80% conversion target); Layer 2 provides granular toggles + policy checkboxes. Sold as premium plugins with per-site licensing, free trials, and annual updates via native marketplaces.

## Target Users & Value Prop
- **Primary**: Agency devs, e-commerce stores, SaaS platforms in EU/CA (strict consent regions).  
- **Pain points solved**: Sequential dialogs causing 20-50% abandonment; dark pattern fines; clunky policy+cookie flows.  
- **Key benefits**: +25% consent rates via optimized UX; geo-targeted banners; one-click admin setup; A/B testable templates.

## Core Features
- Two-layer banner (essential/non-essential toggles, policy links/checkboxes).  
- Geo/IP detection (EU/CA only; configurable).  
- 5+ customizable templates (bottom bar, floating, modal styles).  
- Footer privacy toggle + consent history dashboard.  
- LocalStorage + cookie storage with 12-month persistence.  
- Admin settings: policy URLs, category mapping, button text/localization.

## UX & Compliance Requirements
| Layer | Elements | Compliance Notes |
|-------|----------|------------------|
| **Layer 1** | "We value your privacy" + Accept All/Reject Non-Ess./Settings | No pre-ticked boxes; equal button prominence |
| **Layer 2** | Category toggles + "I accept Privacy Policy" checkbox + Save | Granular opt-in; withdrawal links; no nudging |
| **Both** | Mobile-first, <3s load, policy links open new tabs | CNIL/EDPB/EU cookie subgroup guidelines |

## Technical Architecture
- **Shared**: Vanilla JS/CSS core (no framework deps); 5KB gzipped.  
- **WordPress**: Plugin with WP admin UI, hooks for header/footer, settings API.  
- **Craft CMS**: Composer plugin with CP fields, Twig includes, asset bundles.  
- **Extensibility**: Filter hooks for custom categories; REST API for CMP integrations.

## Monetization & Distribution
- **Pricing**: $79/site initial + $39/year updates (Craft Plugin Store); same via EDD for WP.  
- **Tiers**: Core ($79), Pro ($149: A/B testing, analytics, Zapier) → Enterprise ($299: white-label, SSO).  
- **Marketplaces**: Craft Plugin Store (auto-updates); WP via own site (license validation).  
- **Onboarding**: 5-min wizard + video docs; 30-day trial (watermarked banner).

## Success Metrics & MVP Scope
- **MVP launch**: Single banner → Layered consent → Admin customization (Q1 2026).  
- **KPIs**: 85%+ Layer 1 completion rate; <2% bounce impact; 500 licenses Year 1.  
- **Validation**: Beta with 10 agencies; A/B test vs Cookiebot baseline.  