---
description: WP plugin Iteration 2
globs: ["plugins/consentpro-wp/**/*.php"]
---
# ConsentPro WP Rules [file:3][file:4]

## Scaffold
- consentpro.php: WP6+/PHP7.4, activation options
- Admin: Settings/ConsentPro 5 tabs

## Output
- wp_footer: #consentpro-banner data-config={geo:?,...}
- Hooks: consentpro_config|categories|should_show|assets_url

## License
- AJAX /validate {key,domain,version}
- Cron weekly, 7d grace; is_pro()