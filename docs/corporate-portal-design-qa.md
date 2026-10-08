# Corporate portal design refinement

## Design and scope

This refinement follows the existing public website and admin design language, not a new independent theme. The shared logo, Inter font, Kenya corporate photograph, solid gold primary actions, warm neutral canvas, white surfaces and black typography are reused. All colour values originate in `marketing-tokens.css`; `corporate-portal.css` defines semantic aliases and a shared zero-radius geometry. Admin field borders now alias the same shared token without changing their colour.

Pages covered: company overview, employee membership, portal access, journeys and journey details, issued invoices and invoice details, login, recovery and password setup. Permissions, employer ownership, invitation throttles, archived-account restrictions, financial reads and PDF generation are unchanged.

The company sidebar becomes a horizontally scrollable navigation bar below 800px. Below 560px journey rows become complete readable records; table roles and headers remain available to assistive technology. Other wide directories retain keyboard-focusable scroll regions. The mobile journey layout no longer squeezes dates into narrow columns.

Browser user editors use native `details` controls rather than permanently expanded forms. Validation restores the relevant editor. Shared staff forms retain their existing Bootstrap classes because admin account pages also use them. Password reveal is labelled, keyboard-operable and hidden until JavaScript enhances it. Cancelling employee removal does not send a request or disable its button. Accepted submissions mark their button busy; browser history restoration clears that state.

All new user-visible copy uses translation keys. No invented trends, mock account records, new schema, email transport changes or production data modifications are included.

## Verification

- Portal PHP regression coverage: **21 tests / 266 assertions**, including branded authentication assets, accessible controls, zero-radius/shared-colour rules, role-specific navigation and validation-editor restoration.
- **423 actual isolated browser assertions** at 320, 390, 768, 1280 and 1440px. These cover login/logout, dashboard, employees, portal access/editor expansion, journeys/details, invoices/details/PDF download, finance/viewer navigation, selected-navigation visibility, recovery/setup forms, password reveal, cancelled removal, no page overflow, labelled fields, loaded photographs and native Lucide rendering.
- Dedicated MySQL regression suite: **105 tests / 935 assertions** pass.
- Complete backend suite from a clean detached checkout of UI revision `ab54d5a`: **195 tests / 1,423 assertions**, with two intentional MySQL-only skips. This excludes the concurrent uncommitted search-query edits that briefly failed a separate shared-checkout run; no unrelated search files were modified for this UI task.
- Compiled Blade PHP lint, JavaScript syntax and Git whitespace validation pass.
- Before/after screenshots were captured and inspected; final screenshots are in `/tmp/lala-portal-brand-final`. Browser screenshots alone are not a claim of full accessibility compliance.

To repeat browser validation against the already provisioned isolated stack, use `deploy/acceptance-portal-browser.mjs` as documented in `deploy/CORPORATE_ACCEPTANCE.md`. Secrets are read only from the guarded local acceptance container and are not printed. The reset-page visual check uses a non-functional display-only token; it never activates an identity.

This UI revision is not a production deployment. Production promotion must use the tested immutable release process, retaining corporate bookings disabled and preserving existing accounts and billing data.
