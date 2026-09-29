# Admin UI refinement

## Scope

The shared admin shell and login use a presentation-only refinement layer. It aligns typography, gold primary actions, neutral surfaces, navigation, cards, forms, tables, dialogs and dashboard charts with the website brand. Existing light/dark switching is retained. The footer flows after content instead of covering controls.

Existing square corners are deliberately preserved: the new styles contain no `border-radius` declarations. Existing explicit exceptions, including circular avatars and radio buttons, remain unchanged.

Authentication endpoints, form names, CSRF, permissions, controllers and database logic were not changed. Views using `admin.template` inherit the layer, including modules and any company views sharing that shell. This is not a claim that every individual view has been visually audited.

## Source of truth

- `public/css/marketing-tokens.css`: existing shared brand values.
- `public/css/admin-tokens.css`: admin aliases, semantic colors, layout measurements and dark variants.
- `public/css/admin-refinement.css`: scoped component presentation; local Inter font.
- `resources/views/admin/partials/appearance.blade.php`: cache-busted stylesheet ordering after legacy CSS.
- `resources/lang/en/admin_design.php`: new accessible control labels.

Keep future visual changes in these shared layers. Avoid page-specific brand hex values or blanket radius overrides.

## Local verification

From `Laravel-Backend`:

```sh
php -S 127.0.0.1:8097 -t public scripts/preview-admin.php
node scripts/check-admin-design.cjs
php vendor/bin/phpunit --filter 'AdminAppearanceTest|MarketingViewsTest'
```

The preview exposes dashboard, Add Rider, login and a synthetic component page at `/admin/design-controls`. It uses an in-memory database and synthetic identity, rejects mutations, and is not registered in production routes. **Bind it to loopback only; never deploy this router as an application entry point.**

The browser checker requires Chrome and Playwright (resolved from the sibling prototype, or `PLAYWRIGHT_MODULE_PATH`). Screenshots and computed-style results are written to ignored `design-review/admin/`. It checks desktop/tablet/mobile layouts, both palettes, square controls, round avatars, filtering, pagination, dropdowns, dialogs, sidebar/theme controls and login role selection. Login defaults to light; its dark styling is explicitly exercised by the checker.

Google Maps is intentionally unconfigured in the preview. Add Rider's existing integration therefore raises `google is not defined`; this exact error is allowed only on that fixture. Live Maps, authentication submissions, server-side validation and database-backed workflows still require testing in a configured staging environment. No production deployment is included.
