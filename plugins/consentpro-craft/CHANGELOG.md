# Changelog

All notable changes to ConsentPro for Craft CMS will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-01-10

### Added

- Initial release for Craft CMS 5.x
- Plugin scaffold with PHP 8.2+ requirement
- Settings model with validation for colors, URLs, and license key
- ConsentService for banner configuration and geo-detection
- LicenseService for Pro feature validation with 7-day grace period
- Twig extension with `consentproBanner()` function
- Template variable `craft.consentpro` with `banner()` and `autoInject()` methods
- Geo-detection via Cloudflare CF-IPCountry header (EU/CA targeting)
- Four consent categories: Essential, Analytics, Marketing, Personalization
- Project config storage for multi-environment support
- Control Panel settings page under Settings menu
