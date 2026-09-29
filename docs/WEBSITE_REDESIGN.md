# Public website redesign

The selected homepage is the user's full-photo “Life moves. So do we.” concept, not the earlier split-hero concept. Corporate pages use the photo-led visual language; safety uses the centered-headline concept. The warm city-background section is retained with Kenya-focused messaging.

## Where to edit

- Copy and accessible labels: `resources/lang/en/website.php`. Add matching `website.php` files under other locales to translate; English is the configured fallback. Do not place new literal copy in Blade views or JavaScript.
- Colors, typography scales and radii: `public/css/marketing-tokens.css`.
- Layout/responsive styles: `public/css/marketing.css`.
- Shared layout, header, footer, city section and download dialog: `resources/views/marketing/`.
- Page templates: `home`, `ride`, `drive`, `marketing/business`, `marketing/fleets`, and the public account chooser/company sign-in views.
- Interactions: `public/js/marketing.js`. Uses local Lucide, native dialog and native details.

This is translation-file-managed copy, not a new CMS editor. Existing database-provided brand name, legal links and app-store links remain dynamic. The app download dialog uses configured HTTPS store links and provides an account-creation fallback when links are unavailable.

## Preview and verification

Normal application routes work through the existing Laravel runtime when its database and services are available. A separate read-only view preview is supplied for design review without MySQL:

```sh
php -S 127.0.0.1:8096 -t public scripts/preview-marketing.php
```

Open `http://127.0.0.1:8096/`. This preview is local-only tooling, refuses POST and does not simulate successful authentication. Do not use it as the production entry point.

```sh
php vendor/bin/phpunit --filter MarketingViewsTest
node scripts/check-marketing.cjs
```

The screenshot checker uses Chrome and Playwright installed in the sibling `prototype` workspace. Set `PLAYWRIGHT_MODULE_PATH` to another directory containing Playwright if necessary; `MARKETING_PREVIEW_URL` overrides the preview origin. It checks 12 pages at five widths and writes screenshots to ignored `design-review/website/`.

`node scripts/compare-marketing.cjs` creates side-by-side reference comparisons. Set `MARKETING_REFERENCES` to a directory containing the two selected generated reference PNGs, or copy them to `design-review/website/references/`:

- `exec-0dfd1c97-6c53-45af-ab80-c1d605c97fb8.png` — homepage/business reference.
- `exec-ae225014-39a3-4856-88e3-57df6897ffa3.png` — centered safety composition reference.

See `design-qa.md` for evidence, acceptance boundaries and remaining integration checks.

## Assets

Photography under `public/images/marketing/` was generated for this redesign, not downloaded stock photography. PNG masters are retained; pages serve compressed WebP. The supplied LalaTaxi logo is reused. Inter's SIL Open Font License is in `public/fonts/Inter-LICENSE.txt`.

Art direction / regeneration briefs:

- `corporate-hero`: wide daylight Kenyan city street, leafy left side with copy space, woman approaching branded white sedan, friendly driver holding the door on the right. Used on homepage and business pages.
- `landing-hero`: editorial rider/driver taxi photograph for the split-layout ride page.
- `driver-window`: smiling Kenyan driver in white branded shirt leaning out of a white sedan, trees and city buildings behind; no UI text. Used beside the pale-gold driver CTA.
- `driver-portrait`: professional driver in dark branded polo beside his taxi, city background.
- `business-travel`: professional passenger wearing a seatbelt in the rear of a vehicle.
- `safety-hero`: passenger in a cream outfit checking her phone beside a branded white taxi, natural daylight city setting.
- `nairobi-skyline`: pale warm city silhouette/background. Filename denotes the image, not service-area restrictions; foreground copy says Kenya.

These are summary briefs, not an assertion that the images exactly reproduce the mock's photography.

## Scope and release boundary

Updated: homepage, ride, drive, safety, business, fleets, requirements, driver app guide, driver safety guide, sign-in/signup role selection and company sign-in presentation. The existing company authentication POST action, CSRF field, user type and credential names remain intact.

Unchanged: rider/driver credential forms, signup workflows, company onboarding wizard, authenticated dashboards, mobile apps and production settings. The company system represents fleet operators; copy does not promise an unimplemented employee travel billing system.

Before production release, run actual authentication and registration with a configured database, verify configured store destinations and business claims, and review translated copy. No production deployment is included in this change.
