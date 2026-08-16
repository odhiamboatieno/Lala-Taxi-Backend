# LalaTaxi Backend — Color Usage & Implementation Audit

**Date:** 2026-08-16
**Scope:** Every hard-coded color, CSS variable/SCSS token, and color-bearing JS in the application source.
**Excluded:** `vendor/`, `node_modules/`, `storage/` (third-party / generated). Precompiled third-party bundles in `public/` are summarized (Section 9) rather than enumerated line-by-line; flag icon SVGs and image assets contain incidental color data and are not meaningful brand tokens.

---

## 1. Design tokens (current state)

The only token file in the codebase is `resources/sass/_variables.scss` (Laravel Mix / Bootstrap 4 scaffolding, imported by `resources/sass/app.scss` and compiled to `public/css/app.css`).

### 1.1 Pre-existing Bootstrap/Laravel tokens (`resources/sass/_variables.scss`)

| Token | Value |
|---|---|
| `$body-bg` | `#f5f8fa` |
| `$brand-primary` | `#3097D1` |
| `$brand-info` | `#8eb4cb` |
| `$brand-success` | `#2ab27b` |
| `$brand-warning` | `#cbb956` |
| `$brand-danger` | `#bf5329` |
| `$text-color` | `#636b6f` |
| `$navbar-default-bg` | `#fff` |
| `$input-border` | `lighten($text-color, 40%)` |
| `$input-border-focus` | `lighten($brand-primary, 25%)` |
| `$input-color-placeholder` | `lighten($text-color, 30%)` |
| `$panel-default-heading-bg` | `#fff` |

These compile into `public/css/app.css` (e.g. `#3097d1`, `#636b6f`, and the Bootstrap alert palette `#a94442/#8a6d3b/#3c763d/#31708f`).

### 1.2 LalaTaxi brand palette (added, not yet consumed anywhere)

`resources/sass/_variables.scss` — `$color-*` design tokens:

- **Primary (Lala red):** `$color-primary-50 #FFF1F3`, `-100 #FFD9DF`, `-200 #FFB0BC`, `-300 #FF7D91`, `-400 #FF4C68`, `-500 #FF214A` (brand base), `-600 #E01640`, `-700 #B21134`, `-800 #850C27`, `-900 #59081A`
- **Secondary (Lala amber):** `$color-amber-50 #FFF9EC`, `-100 #FFEECB`, `-200 #FFDF9E`, `-300 #FFCE71`, `-400 #FFC565`, `-500 #FFBD59` (brand base), `-600 #F0A83A`, `-700 #D68E22`, `-800 #A66E1A`, `-900 #764F13`
- **Neutral:** `$color-neutral-50 #FAFAFA`, `-100 #F4F4F5`, `-200 #E4E4E7`, `-300 #D1D1D6`, `-400 #A1A1AA`, `-500 #71717A`, `-600 #52525B`, `-700 #3F3F46`, `-800 #27272A`, `-900 #0A0A0A`
- **Semantic:** `$color-success #16A34A`, `$color-warning #A66E1A`, `$color-error #DC2626`, `$color-info #2563EB`

> None of the new palette values are referenced by any stylesheet, Blade template, or JS yet — the tokens exist only in the SCSS file.

---

## 2. De-facto brand colors in use today (the clash drivers)

These are the colors the app actually renders with across the web/landing CSS:

| Color | Value | Where |
|---|---|---|
| **Cyan (primary accent)** | `#1fbad6` | `public/css/main.css` (47×), `common.css` (12×), `home.css` (7×), `payment.css`/`webpayment.css` (6×), `common1.css` (2×), views: `common/header`, `site_settings`, `dashboard/referral`, `driver_dashboard/referral`, `user/signup_driver`, `signup_company` |
| **Dark navy** | `#02172d` | `main.css` (24×), `home.css` (22×), `common.css` (20×), views: `common/dashboard_side_menu`, `driver_dashboard_side_menu`, `payment.css`, `webpayment.css` |
| **Yellow accent** | `#f6c90f` | `common.css` (9×), `main.css` (3×), `home.css` (1×) |
| **Laravel scaffold blue** | `#3097d1` | `public/css/app.css` (25×) + mail theme |
| **Laravel scaffold gray** | `#636b6f` | `app.css` (8×), `welcome.blade.php`, `web_payment.blade.php` |
| **Theme purple** | `#5143d9` | `public/css/mainstyle.css` (39×) |
| **Theme red** | `#d6293e` | `mainstyle.css` (21×) |
| **Theme dark** | `#0b0a12` | `mainstyle.css` (48×) |
| **Admin reskin teal** | `#1c6b72` / `#1c6c7c` | `admin-dashboard-reskin.css`, `account-dashboard-reskin.css` |
| **Admin reskin orange** | `#c76836` / `#c55f36` | same reskin files |
| **DataTables teal** | `#14b8a6` | `public/css/admin-datatables.css` |

The new Lala red `#FF214A` / amber `#FFBD59` do **not** appear anywhere in the codebase.

---

## 3. `resources/sass` (SCSS source)

- `resources/sass/_variables.scss` — all tokens (Section 1).
- `resources/sass/app.scss` — imports fonts + variables + Bootstrap; **no colors defined directly**.
- Compiled result: `public/css/app.css`.

---

## 4. `public/css` — app-owned stylesheets (complete unique-color inventory)

Counts = occurrences per file.

### `public/css/app.css` (501 matches)
`#000`(16) `#00000000`(2) `#090909`(6) `#0d3625`(2) `#101010`(1) `#133d55`(2) `#196c4b`(2) `#1f648b`(2) `#20895e`(3) `#216a94`(3) `#222`(1) `#245269`(2) `#2579a9`(5) `#259d6d`(2) `#262626`(1) `#2a88bd`(2) `#2ab27b`(5) `#2b542c`(3) `#3097d1`(25) `#31708f`(9) `#333`(13) `#3c763d`(14) `#3d6983`(2) `#411c0e`(2) `#444`(3) `#538db0`(2) `#555`(8) `#5e5e5e`(2) `#636b6f`(8) `#66512c`(3) `#67b168`(1) `#685d20`(2) `#6b9dbb`(3) `#777`(22) `#78341a`(2) `#7da8c3`(2) `#80000000`(2) `#843534`(3) `#888`(1) `#8a6d3b`(14) `#8c8c8c`(2) `#8eb4cb`(5) `#954120`(3) `#98cbe8`(1) `#999`(6) `#9b8a30`(2) `#9d9d9d`(5) `#a4aaae`(1) `#a6e1ec`(1) `#a94442`(14) `#aa4a24`(2) `#adadad`(1) `#afd9ee`(1) `#b1b7ba`(3) `#b6a338`(3) `#bce8f1`(5) `#bf5329`(5) `#c0a16b`(1) `#c1e2b3`(1) `#c4e3f3`(2) `#c5b143`(2) `#c7254e`(1) `#c9e2b3`(1) `#cbb956`(5) `#ccc`(9) `#ccd0d2`(2) `#ce8483`(1) `#d0e9c6`(2) `#d3e0e9`(10) `#d4d4d4`(1) `#d5d5d5`(1) `#d6e9c6`(5) `#d7ebf6`(1) `#d9edf7`(6) `#ddd`(21) `#dff0d8`(7) `#e3e3e3`(1) `#e4b9b9`(1) `#e4b9c0`(1) `#e5e5e5`(5) `#e6e6e6`(2) `#e8e8e8`(1) `#ebcccc`(2) `#ebccd1`(5) `#ebebeb`(1) `#eee`(17) `#f2dede`(7) `#f5f5f5`(9) `#f5f8fa`(6) `#f7e1b5`(1) `#f7ecb5`(1) `#f7f7f7`(1) `#f9f2f4`(1) `#f9f9f9`(1) `#faebcc`(5) `#faf2cc`(2) `#fcf8e3`(8) `#ff0`(1) `#fff`(80)

### `public/css/common.css` (193)
`#000`(9) `#000000`(6) `#00000029`(2) `#02172d`(20) `#1ca7c0`(4) `#1fbad6`(12) `#23527c`(1) `#285228`(2) `#2b2a2a`(4) `#333`(1) `#377037`(7) `#3a4750`(4) `#3b5998`(2) `#484848`(2) `#565a5c`(4) `#5bd1e6`(1) `#5f5f5f`(1) `#717171`(1) `#757575`(3) `#828282`(1) `#82888a`(5) `#919293`(5) `#a6a5a5`(1) `#aaa`(1) `#b88b21`(1) `#c0c0c0`(2) `#c4c4c4`(1) `#c6c6c6`(4) `#c7c7c7`(3) `#cacccd`(3) `#ccc`(5) `#d2d2d2`(1) `#d9d9d9`(1) `#dce0e0`(5) `#ddd`(1) `#ddf2ff`(2) `#e5e5e4`(4) `#ebeceb`(1) `#edefed`(1) `#eeeeef`(1) `#f1f1f1`(1) `#f2f2f2`(1) `#f5f5f5`(1) `#f6c90f`(9) `#f8f8f9`(3) `#fff`(30) `#ffffff`(13)

### `public/css/home.css` (59)
`#000`(2) `#00000029`(1) `#02172d`(22) `#1fbad6`(7) `#3a4750`(4) `#c7c7c7`(1) `#ddf2ff`(10) `#f6c90f`(1) `#fff`(5) `#ffffff`(5) `#ffffff2e`(1)

### `public/css/main.css` (275)
`#000`(12) `#000000`(1) `#02172d`(24) `#09091a`(1) `#11939a`(2) `#1ca7c0`(1) `#1fbad6`(47) `#27aa0b`(3) `#282727`(1) `#337ab7`(1) `#3a4750`(4) `#444`(2) `#444444`(1) `#494949`(2) `#4db5d9`(1) `#6dbd63`(1) `#717171`(2) `#929292`(2) `#939393`(1) `#95959529`(1) `#97c5f9`(1) `#999`(1) `#aaa`(4) `#c0c0c0`(2) `#c1c3c4`(1) `#c2c2c2`(2) `#c6c6c6`(10) `#c7c7c7`(2) `#ccc`(7) `#cccccc`(1) `#d6d6d5`(3) `#ddd`(6) `#ddf2ff`(6) `#e1e1e1`(4) `#e5e5e4`(14) `#eee`(13) `#efefef`(10) `#f1f1f1`(5) `#f32f00`(2) `#f3f3f3`(2) `#f3f3f4`(3) `#f62419`(2) `#f6c90f`(3) `#f6c90f59`(2) `#f8f8f9`(2) `#fafafa`(2) `#ffc107`(2) `#fff`(48) `#ffffff`(5)

### `public/css/mainstyle.css` (727)
`#000`(73) `#031633`(1) `#032830`(1) `#051b11`(1) `#08070d`(1) `#084298`(1) `#087990`(1) `#08845f`(1) `#098d65`(1) `#0a966c`(2) `#0aa073`(1) `#0b0a12`(48) `#0cbc87`(18) `#0d6efd`(1) `#0f5132`(1) `#107181`(1) `#117a8a`(1) `#128293`(2) `#148a9c`(1) `#161719`(1) `#17a2b8`(18) `#191b1d`(1) `#198754`(2) `#1a1d20`(1) `#1d3b53`(1) `#207eb4`(2) `#20c997`(1) `#212529`(3) `#21be5c`(2) `#222529`(2) `#23232a`(2) `#238cc8`(2) `#25d366`(2) `#285aeb`(8) `#2a2c31`(1) `#2b3035`(1) `#2c0b0e`(1) `#2c3034`(1) `#302f36`(1) `#323539`(1) `#332701`(1) `#343a40`(3) `#34a853`(1) `#3672d9`(2) `#373b3e`(1) `#392f98`(1) `#3aacdd`(2) `#3c3b41`(1) `#3c7ff1`(2) `#3d32a3`(1) `#40bff5`(2) `#4136ae`(4) `#41464b`(1) `#4285f4`(1) `#4539b8`(1) `#464950`(1) `#493cc3`(1) `#495057`(2) `#4d5154`(1) `#5143d9`(39) `#5475bc`(2) `#5d5f61`(1) `#5d82d1`(2) `#5e5e5f`(3) `#5f5f68`(1) `#646568`(1) `#6610f2`(1) `#664d03`(1) `#6a6c6e`(2) `#6c757d`(2) `#6ea8fe`(1) `#6edff6`(1) `#6f42c1`(1) `#717375`(1) `#747579`(8) `#75b798`(3) `#7a6deb`(1) `#842029`(1) `#85878a`(23) `#8e85e6`(3) `#961d2b`(1) `#96969a`(5) `#997404`(1) `#9d9da0`(1) `#9ee4cf`(1) `#a11f2f`(1) `#a1a1a8`(1) `#a2dae3`(1) `#a6b5cc`(1) `#a6c3ca`(1) `#a7acb1`(1) `#a7b9b1`(1) `#ab2132`(2) `#ad8920`(1) `#af2767`(2) `#b0b0b8`(1) `#b5b6b7`(1) `#b62335`(1) `#b6d4fe`(2) `#b8b8b9`(1) `#b9b4f0`(1) `#bacbe6`(1) `#badce3`(1) `#bcd0c7`(1) `#be5f0f`(1) `#bfc0c9`(1) `#bfd1ec`(1) `#bfe2e9`(1) `#c1d6cc`(1) `#c22b72`(2) `#c4c4c5`(2) `#c5c5c7`(7) `#c5c6cc`(1) `#c5d7f2`(1) `#c5e8ef`(1) `#c6acae`(1) `#c6c7c8`(1) `#c7dbd2`(1) `#ca6510`(2) `#cbccce`(1) `#ccc2a4`(1) `#ceced0`(1) `#cecfd0`(1) `#cef2e7`(1) `#cfe2ff`(1) `#cff4fc`(1) `#d0d0d1`(1) `#d1d2d4`(1) `#d1e7dd`(1) `#d1ecf1`(1) `#d6249f`(8) `#d6293e`(21) `#d63384`(1) `#d76b11`(1) `#d7d8da`(1) `#dc3545`(2) `#dcd9f7`(1) `#dee2e6`(3) `#dfc2c4`(1) `#dfdfe3`(7) `#dfe0e1`(1) `#e2e3e5`(1) `#e5c7ca`(1) `#e5e6e7`(1) `#e60000`(2) `#e685b5`(1) `#e6dbb9`(1) `#e7e7e8`(1) `#e83e8c`(1) `#ea4335`(1) `#ea868f`(3) `#eccccf`(1) `#ece1be`(1) `#ecedee`(1) `#efa9b2`(1) `#f2e7c3`(1) `#f5f5f6`(19) `#f7c32e`(18) `#f7d4d8`(1) `#f8c943`(2) `#f8cc4d`(1) `#f8d7da`(1) `#f8f9fa`(2) `#f9cf58`(1) `#fafafb`(1) `#fbbc05`(1) `#fce7ab`(1) `#fd5949`(8) `#fd7e14`(18) `#fdf3d5`(1) `#fdf497`(16) `#ff0000`(2) `#ffda6a`(1) `#fff`(160) `#fff3cd`(2) `#ffffffb3`(1) `#ffffffd9`(1)

### `public/css/landing-dispatch.css` (41)
`#07111f`(2) `#08111f`(4) `#0b1322`(1) `#0b1422`(1) `#0d1828`(2) `#0d1a2d`(1) `#10203a`(1) `#112646`(1) `#1497af`(1) `#16120a`(1) `#16965d`(1) `#1fbad6`(1) `#3dd598`(1) `#4f5f75`(1) `#5ce3a8`(1) `#eef4f8`(1) `#f4f8fb`(1) `#f6c445`(1) `#ffd86b`(1) `#ffde7e`(1) `#ffffff`(16)

### `public/css/admin-dashboard-reskin.css` (27)
`#17202b` `#18232f` `#1c6b72`(2) `#1f2430` `#1f2832` `#1f2a36` `#223040` `#23424a` `#24384a` `#6c7280` `#7d2222` `#8f3e16` `#b64646` `#c76836` `#efe6db` `#f4efe7` `#f7f1e8` `#fff`(8) `#fffdf9`

### `public/css/account-dashboard-reskin.css` (24)
`#174956`(2) `#18222d` `#1a2430` `#1c6c7c`(3) `#1f2a36`(2) `#20262f` `#213344` `#22444d` `#596171` `#8f3e16`(2) `#c55f36`(2) `#ece6db` `#f5f7fa` `#f7f4ee` `#fff`(4)

### `public/css/admin-datatables.css` (15)
`#0f172a` `#0f766e` `#111827`(2) `#14b8a6`(3) `#64748b` `#94a3b8` `#e5e7eb`(2) `#f1f5f9` `#f8fafc` `#fff`(2)

### `public/css/payment.css` (32) — same set as `webpayment.css`
`#000` `#02172d` `#1fbad6`(6) `#222`(4) `#222222` `#31325f` `#333` `#8898aa` `#cfd7e0`(2) `#e4584c` `#f0f5fa` `#fafafa` `#fff`(10) `#ffffff78`

### `public/css/webpayment.css` (32)
Identical palette to `payment.css` above.

### `public/css/popup.css` (2)
`#000` `#fff`

### `public/css/common1.css` (12)
`#000` `#02172d`(2) `#1fbad6`(2) `#636363` `#ccc`(3) `#eee` `#fff`(2)

---

## 5. `public/css` — third-party files (not enumerated)

| File | Hex matches | Source |
|---|---|---|
| `bootstrap.css` / `bootstrap.min.css` | ~530 each | Bootstrap 3 |
| `font-awesome.min.css` | 2 | FontAwesome |
| `jquery-ui.min.css` | 34 | jQuery UI |
| `jquery.bxslider.css` | 11 | bxSlider |
| `jquery.sliderTabs.min.css` | 43 | jQuery SliderTabs |
| `selectize.default.css` | 79 | Selectize |
| `buttons.dataTables.css` | 98 | DataTables |

---

## 6. Blade templates (`resources/views`) — hard-coded colors

84 view files contain hard-coded hex colors (899 total matches). Complete per-file inventory:

- `admin/api_credentials.blade.php` — `#495057 #6c757d #e9ecef`
- `admin/cancel_reason/add.blade.php` — `#dee2e6 #e3e6f0 #f8f9fa`
- `admin/cancel_reason/edit.blade.php` — `#dee2e6 #e3e6f0 #f8f9fa`
- `admin/common/header.blade.php` — `#000 #1dd1a1 #1e3c72 #2a5298 #303841 #48dbfb #feca57 #ff6b6b #fff`
- `admin/company/edit.blade.php` — `#0f172a #0f766e #15803d #2563eb #475569 #64748b #b45309 #b91c1c #f8fafc #fff`
- `admin/fees.blade.php` — `#f8f9fa`
- `admin/heat_map.blade.php` — `#23395d #27b7a8 #2f86c3 #334155 #475569 #64748b #eef2f6 #f3f4f6 #f4f7fb #f8fafc #fff`
- `admin/index.blade.php` — `#06b6d4 #15b8a6 #16375a #17a398 #183b68 #18aa94 #19a78f #1d3d67 #1f3554 #22c55e #31c471 #35c5a8 #37c3af #3b82f6 #3ca9f4 #40627f #4678d8 #4e6880 #4fd1c5 #5875f5 #5c97ff #658098 #68809b #69839c #6b8096 #7c9bff #8196aa #8aa1b7 #9c6aff #aa6df4 #d18fff #d39424 #d8e8ef #dcecf1 #dfeff3 #ebf4f7 #edf3f8 #ee5d69 #ef4444 #efc94a #f18a47 #f2f8fb #f3d879 #f59e0b #f6fbfd #f9ab62 #ff5f6d #ff7278 #ff7b81 #ffad5b #ffb000 #ffd43b #fff #ffffff`
- `admin/language/change_lanuage.blade.php` — `#007bff #dee2e6 #f8f9fa`
- `admin/locations/page.blade.php` — `#0f766e #14b8a6 #2563eb #f8fbff #fe2c2c #fff`
- `admin/login.blade.php` — `#065f46 #07111f #0f766e #102033 #115e59 #123454 #1d4ed8 #1e3c72 #2563eb #334155 #64748b #94a3b8 #991b1b #d1fae5 #e2e8f0 #e63946 #f1f5f9 #fee2e2 #ffffff`
- `admin/manual_booking/index.blade.php` — `#0d6efd #0f172a #15803d #198754 #1d4ed8 #212529 #222c31 #334155 #475569 #495057 #6c757d #842029 #856404 #b45309 #b6d4fe #b91c1c #cbd5e1 #ced4da #ced7e2 #dbe4ee #dc3545 #dee2e6 #e2e8f0 #e7f1ff #e9ecef #eef2f7 #f3f3f3 #f5c2c7 #f8d7da #f8f9fa #f8fafc #ffc107 #fff #fff3cd #ffffff`
- `admin/map.blade.php` — `#23395d #27b7a8 #414b5f #4a5568 #64748b #7a3f3f #dff7f1 #f4f7fb #ff5c5c #ff9f43 #fff #fff5f5` (+ `rgba(16,24,40,.08)` ×7, `rgba(255,92,92,.15)`)
- `admin/modules/index.blade.php` — `#10b981 #f59e0b`
- `admin/modules/upload.blade.php` — `#0f172a #214ba2 #64748b #cbd5e1 #e2e8f0 #eef3ff #f8fafc`
- `admin/notifications/index.blade.php` — `#0d6efd #31c8b1 #6c757d #fff`
- `admin/payment_gateway.blade.php` — `#0f172a #2c3239 #334155 #9da1ab #dbe3ef #f8fafc #fff`
- `admin/payments/receipt_pdf.blade.php` — `#ffffff`
- `admin/payments/receipt_print.blade.php` — `#f4f7fb`
- `admin/payments/receipt_sheet.blade.php` — `#0f172a #4f46e5 #6366f1 #64748b #94a3b8 #e2e8f0 #e5e7eb #edf2f7 #f8fafc #fff`
- `admin/payments/view.blade.php` — `#159947 #16a34a #18233d #182848 #1a2544 #1c2642 #2563eb #4f46e5 #6c7898 #7b88a8 #b7791f #d53f3f #e6ebf4 #e8eef7 #e8fbef #edf1f7 #ef4444 #f4f7fb #f8fafc #ffe7e7 #fff #fff7df`
- `admin/request/details.blade.php` — `#e0e0e0`
- `admin/roles/add.blade.php` — `#495057 #dee2e6 #e0e0e0 #f9f9f9`
- `admin/roles/edit.blade.php` — `#e0e0e0 #f9f9f9`
- `admin/send_message.blade.php` — `#11907c #2b78c7 #6c757d #8b6b10 #cb8212 #f4df99 #f8f9fa #fff #fff3cd`
- `admin/site_settings.blade.php` — `#1fbad6 #ccc #f8f9fa`
- `admin/template.blade.php` — `#16a34a #334155 #d97706 #dc2626 #fff`
- `admin/trips/index.blade.php` — `#0f172a #0f766e #16a34a #22c55e #2563eb #64748b #dc2626 #f8fafc #fff #ffffff`
- `admin/trips/view.blade.php` — `#f3f4f6 #f9fafb #ffffff`
- `admin/vehicle/add.blade.php` — `#defa`
- `admin/vehicle/edit.blade.php` — `#defa`
- `common/dashboard_footer.blade.php` — `#000`
- `common/dashboard_header.blade.php` — `#3c763d #dff0d8`
- `common/dashboard_side_menu.blade.php` — `#02172d`
- `common/driver_dashboard_footer.blade.php` — `#000`
- `common/driver_dashboard_side_menu.blade.php` — `#02172d`
- `common/footersign.blade.php` — `#000`
- `common/header.blade.php` — `#1fbad6 #e5e5e4 #fff`
- `common/help_header.blade.php` — `#216c55`
- `common/signin_footer.blade.php` — `#000`
- `dashboard/download_invoice.blade.php` — `#c2c2c2`
- `dashboard/download_rider_invoice.blade.php` — `#c2c2c2`
- `dashboard/referral.blade.php` — `#1fbad6`
- `dashboard/trip.blade.php` — `#ddd`
- `drive/drive_safety.blade.php` — `#000 #494949 #a6daec #e3e3e3 #f1f1f1 #f8f8f9 #fff`
- `drive/driver_app.blade.php` — `#a6daec #c6c6c6 #f8f8f9 #fff`
- `drive/requirements.blade.php` — `#a6daec #f1f1f1 #f8f8f9 #fff`
- `driver_dashboard/add_vehicle.blade.php` — `#2ec5e1 #555 #ddd #f7f7f7`
- `driver_dashboard/driver_banking.blade.php` — `#000 #bc1218`
- `driver_dashboard/driver_payment.blade.php` — `#fff`
- `driver_dashboard/driver_trip.blade.php` — `#ddd`
- `driver_dashboard/referral.blade.php` — `#1fbad6`
- `emails/common/header.blade.php` — `#000000 #565a5c #62cead #ddf2ff #f7f7f7 #ffffff`
- `emails/forgot_password.blade.php` — `#12939a #717172 #ffffff`
- `errors/503.blade.php` — `#b0bec5`
- `home/apps.blade.php` — `#f8f8f9 #fff`
- `payment/braintree.blade.php` — `#222222 #fff`
- `payment/loader.blade.php` — `#abbd81 #e15b64 #f47e60 #f8b26a`
- `payment/mpesa.blade.php` — `#218838 #28a745 #ccc #f3f3f3`
- `payment/stripe.blade.php` — `#222222 #666ee8 #cfd7e0 #fff`
- `ride/how_it_works.blade.php` — `#285228 #c6c6c6 #f8f8f9 #ffc107 #fff`
- `ride/ride.blade.php` — `#01579b #0288d1 #212121 #4fc3f7 #616161 #a6daec #cab #e3f2fd #f1f1f1 #f5f5f5 #f8f8f9 #ffc107 #fff #ffffff`
- `ride/safety.blade.php` — `#a6daec #f8f8f9 #fff`
- `templatesign.blade.php` — `#020617 #0f172a #22c55e #e5e7eb`
- `user/company_account_under_review.blade.php` — `#081321 #0f172a #111827 #14b8a6 #99f6e4 #fff`
- `user/company_onboarding_wizard.blade.php` — `#081321 #0f172a #0f766e #166534 #1d4ed8 #2563eb #334155 #475569 #64748b #7f1d1d #92400e #94a3b8 #b91c1c #bfdbfe #cbd5e1 #ccfbf1 #d9e2ec #dcfce7 #e2e8f0 #eef3f8 #eff6ff #f5f7fb #f8fafc #fecaca #fee2e2 #fef3c7 #fff #fff7f7`
- `user/driver_deletion.blade.php` — `#3e3e3e #939393`
- `user/forgot_password.blade.php` — `#3e3e3e #939393 #f1f1f1`
- `user/login.blade.php` — `#ddf2ff`
- `user/reset_password.blade.php` — `#3e3e3e #939393`
- `user/rider_deletion.blade.php` — `#3e3e3e #6e9af7 #939393 #fff`
- `user/signin_company.blade.php` — `#07111f #0f172a #0f766e #123454 #166534 #2563eb #334155 #64748b #991b1b #dbe3ef #dc2626 #dcfce7 #ecfeff #fee2e2 #fff #ffffff`
- `user/signin_driver.blade.php` — `#3e3e3e #939393`
- `user/signin_rider.blade.php` — `#3e3e3e #6e9af7 #939393 #fff`
- `user/signup_company.blade.php` — `#0f766e #111827 #1fbad6 #334155 #64748b #ccfbf1 #dbe3ef #dc2626 #e2e8f0 #e5e7eb #f8fafc #fff`
- `user/signup_driver.blade.php` — `#1fbad6 #e5e5e4 #fff #ffffff`
- `user/signup.blade.php` — `#0288d1 #212121 #616161 #e0e0e0 #ffffff`
- `vendor/mail/html/themes/default.css` — `#2ab27b #2f3133 #3097d1 #3869d4 #74787e #9ba2ab #aeaeae #bbbfc3 #bf5329 #edeff2 #f2f4f6 #f5f8fa #fff #ffffff` (Laravel mail theme)
- `web_payment.blade.php` — `#636b6f #fff`
- `welcome.blade.php` — `#636b6f #fff`

### 6.1 Most-used hex values across all views

`#fff`(104) `#ffffff`(34) `#64748b`(28) `#0f172a`(27) `#f8f8f9`(22) `#e2e8f0`(22) `#f8fafc`(21) `#0f766e`(20) `#2563eb`(18) `#334155`(15) `#23395d`(14) `#000`(13) `#0288d1`(12) `#6c757d`(11) `#3e3e3e`(11) `#212121`(11) `#f8f9fa`(10) `#939393`(10) `#1f3554`(10) `#94a3b8`(9) `#0d6efd`(9) `#27b7a8`(8) `#a6daec`(7) `#fe2c2c`(6) `#f1f1f1`(6) `#e5e7eb`(6) `#dee2e6`(6) `#616161`(6) `#222222`(6) `#1fbad6`(6) `#edeff2`(5) `#dc2626`(5) `#cbd5e1`(5) `#bf5329`(5) `#b91c1c`(5) `#8aa1b7`(5) `#475569`(5) `#35c5a8`(5) `#3097d1`(5) `#2ab27b`(5)

### 6.2 rgb()/rgba() used in views (top)

`rgba(16,24,40,.08)`(15) `rgba(0,0,0,.125)`(11) `rgba(0,0,0,.1)`(9) `rgb(255,255,255)`(5) `rgb(248,248,249)`(5) `rgba(20,33,61,.08)`(4) `rgba(15,23,42,.05)`(4) `rgba(0,0,0,.18)`(4) `rgba(0,0,0,.15)`(4) `rgba(34,197,94,0)`(3) `rgba(255,255,255,.84)`(3) `rgba(255,255,255,.18)`(3) `rgba(255,255,255,.12)`(3) `rgba(15,23,42,.12)`(3) `rgba(15,23,42,.08)`(3) `rgba(148,163,184,.22)`(3) `rgba(148,163,184,.18)`(3) `rgba(50,199,170,.14)`(2) `rgba(37,99,235,.12)`(2)

### 6.3 Named color keywords in views

`gray`(86) `white`(64) `green`(61) `blue`(60) `transparent`(47) `red`(36) `black`(19) `yellow`(14) `purple`(10) — used in class names (`text-green`, `bg-gray-100`, etc.) and inline styles.

---

## 7. JavaScript color usage

Color values live in chart/dashboard/map JS. App-owned files:

- `public/assets/js/chart/apexcharts-line.init.js` — ApexCharts palettes: `#77B6EA #545454 #00E396 #775DD0 #008FFB #FF4560 #304758 #D8E3F0 #BED1E6 #e7e7e7 #f1f1f1`
- `public/assets/js/dashboard/logistic.init.js` (14) — chart colors incl. `#16a2b8` (app cyan) `#ffc108` `#0d6efd` etc.
- `public/assets/js/dashboard/e-commerce.init.js` (14), `dashboard/crm.init.js` (12), `dashboard/project.init.js` (7), `dashboard/analytics.init.js` (6), `dashboard/academy.init.js` (6), `app/academy-single-category.init.js` (8), `app/project-overview.init.js` (4), `chart/echart.init.js` (5), `map/vector-maps.init.js` (3)
- `public/js/user.js` (2), `public/js/accountkit.js` (2)

Unique colors across app-owned JS: `#00e396`(6) `#008ffb`(5) `#ff4560`(4) `#16a2b8`(4) `#ffc108`(3) `#0d6efd`(3) `#007bff`(3) `#f59e0b`(2) `#f46a6a`(2) `#f27333`(2) `#ec0000`(2) `#d4526e`(2) `#adb5bd`(2) `#6d28d9`(2) `#556ee6`(2) `#34c38f`(2) `#33b2df`(2) `#28a745`(2) `#00da3c`(2) plus single-use `#ffe0b2 #ffd699 #ffd3b5 #ffcc80 #ffc3a0 #ffc107 #ffb74d #ff5733 #f57433 #f1f1f1 #f1c40f` …

Third-party JS bundles containing incidental color data (not app tokens): `public/js/sweetalert.min.js` (22), `public/js/jquery-ui.js` (18), `public/js/angular.js` (6).

---

## 8. PHP / config / database

- **PHP:** No real color constants. The only `#…` matches in `app/` are order IDs in `MollieController.php` (`"Order #12345"`), not colors.
- **Database seeders:** No real colors. `CurrencyTableSeeder.php` (`#165`, `#8361`, …) are currency ISO/code fragments; `NotificationDispatchLogsTableSeeder.php` (`#100`, `#101`, …) are notification IDs; `PagesTableSeeder.php` embeds `#000000` inside demo page HTML content only.

---

## 9. Third-party / precompiled theme bundles (`public/assets`, `public/admin_assets`)

These are shipped framework themes, not authored in this repo:

- `public/assets/css/app.min.css` / `app-rtl.min.css` — ~397 hex matches each (admin panel theme; includes its own palette — e.g. teal `#0c9a94`-family, `#1a5cff`-family accents). Precompiled; no SCSS source in this repo.
- `public/assets/css/bootstrap.min.css` (+ rtl) — Bootstrap (~329 each).
- `public/assets/css/remixicon.*`, `bootstrap-icons.*` — icon fonts/SVG (incidental).
- `public/assets/images/flag/*.svg` — 200+ country flag SVGs with embedded palette data (not brand tokens).
- `public/admin_assets/` — AdminLTE theme: `bootstrap/css/bootstrap.css` (~530), `build/bootstrap-less/variables.less` (110 token values incl. AdminLTE brand `#00a65a`, `#dd4b39`, `#3c8dbc`), `build/less/*.less` (theme component styles).
- `public/admin_assets/bootstrap/css/bootstrap.css.map` — source map, not runtime.

---

## 10. Observations

1. **The new brand palette is not wired in anywhere.** Lala red `#FF214A` and Lala amber `#FFBD59` occur 0 times; the tokens from `_variables.scss` are unused so far. The app renders with the legacy accents instead: **cyan `#1fbad6`** + **navy `#02172d`** + **yellow `#f6c90f`** on the web side, and a **purple `#5143d9` / red `#d6293e` / dark `#0b0a12`** theme in `mainstyle.css` (used by the public web theme).
2. **Three very different brand systems coexist**: (a) Laravel scaffolding (`#3097d1`/`#636b6f` in `app.css`, mail theme), (b) the taxi web CSS family (`#1fbad6`/`#02172d`/`#f6c90f` across `main.css`, `common.css`, `home.css`, `payment.css`, side menus, headers, referrals), and (c) newer Tailwind-styled admin screens using the slate palette (`#0f172a`, `#64748b`, `#e2e8f0`, `#2563eb`) with teal status accents (`#14b8a6`, `#0f766e`).
3. **Some new-palette semantic values already match existing usage by coincidence** (Tailwind defaults): `#16a34a` (green/success) appears in `admin/template`, `admin/trips/index`, `admin/payments/view`; `#dc2626` (red/error) appears in `admin/template`, `admin/trips/index`, `user/signin_company`, `signup_company`; `#2563eb` (blue/info) appears widely. These can be aligned with `$color-success`/`$color-error`/`$color-info` with little friction.
4. **Hard-coded values dominate** — only `_variables.scss` uses tokens; every stylesheet, Blade inline style, and chart config hard-codes colors. There is no CSS-variable layer and no Tailwind config.
5. Files with the heaviest color loads to tackle first if migrating to tokens: `public/css/mainstyle.css` (727), `public/css/app.css` (501), `public/css/main.css` (275), `public/css/common.css` (193), `resources/views/admin/index.blade.php` (91), `resources/views/admin/manual_booking/index.blade.php` (82), `resources/views/user/company_onboarding_wizard.blade.php` (55), `resources/views/ride/ride.blade.php` (50).