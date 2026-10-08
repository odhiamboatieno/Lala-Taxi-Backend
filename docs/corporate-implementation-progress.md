# Corporate implementation progress

Source specification: workspace-root `CORPORATE_ACCOUNTS_IMPLEMENTATION.md` (corrected 2026-10-07).

## Current execution status — 2026-10-08

- Phases 0–7 are implemented and pushed: schema/domain, booking attribution/dispatch, Deferred accounting, invoicing/settlement, admin/company management, JWT APIs, Customer and Driver integration.
- Phase 8 automated backend/mobile contracts and session regressions pass. Latest local totals: backend 155 tests / 881 assertions (two MySQL-only skips); dedicated MySQL 78 tests / 630 assertions before the latest bootstrap/runtime follow-ups, followed by the updated release subset (7 tests / 24 assertions); independent full-schema provisioning (2 tests / 23 assertions); Customer 33 Flutter tests; Driver 24 Flutter tests. Both Flutter analyzers and Android debug builds pass. These suites overlap; their counts are not a combined unique-test total.
- Phase 9 release safeguards are implemented. CI run [37756585735](https://github.com/odhiamboatieno/Lala-Taxi-Backend/actions/runs/37756585735), commit `b42f27838342fc6acf6b4f473ee2fc382a99f7c6`, completed successfully: tests, Docker build/image publication and deployment-bundle packaging. Publication is not production deployment.
- Live acceptance is underway; controlled rollout remains incomplete. Fictional Mwangaza Workspaces Kenya and Tawi Coast Supplies identities are provisioned in a separate local Docker stack. All 75 real HTTP authentication/role/session checks pass. Scheduler tasks and Redis queue processing are observed, and Reverb starts. Invoice transport will be checked through non-relaying Mailpit, not real company mailboxes.
- Remaining evidence includes actual booking/completion/finance delivery, device GPS/push and personal-flow regressions, plus a current backup and verified restore before production promotion. The Android device is connected and a staging Driver ARM64 build passes; native acceptance has not yet passed. Production corporate bookings remain disabled; no production financial mutations or deployment are claimed.

The sections below preserve each phase's historical verification checkpoint; use this summary and the final follow-ups for the latest counts and CI result.

## Phase 0 — Prerequisites

- Static SaaSDispatch registration; explicit cached corporate admin/company routes and tenant context.
- Platform and enabled tenant capability checks, company-scoped invoice validation, collision-resistant header numbers.
- Admin search/navigation and company-specific form destinations.
- Unsupported manual card/wallet/invoice choices rejected before rider creation; Cash preserved through schedule dispatch and validated draft/template input. Corporate payment will be added with validated membership in Phase 2.
- Request estimates widened additively to DECIMAL(12,2); rollback deliberately retains the safe widened range.
- Legacy physical completion records completed_at; fare calculation uses rider currency/promotion context rather than completing driver's context.
- Existing generic duplicate route names corrected without changing their URLs or the previously winning named routes. PHPUnit configuration upgraded to remove its deprecation.

Verification: full PHPUnit suite, new tenant/capability/manual-payment regressions, PHP/compiled Blade syntax and isolated route cache. Fresh core migrations passed against isolated MySQL 8.4; trip status collation is utf8mb4_unicode_ci there (not a production claim).

## Phase 1 — Schema and domain

- Additive, retry-safe migration with legacy ownership preflight, correctly sized restrictive FKs and composite invoice/account ownership constraint.
- Account bill-to/currency/timezone/terms/archive fields; rider affiliation/roles; schedule/request/trip immutable-context fields; fixed finalized charges and Deferred enum support.
- Retained invoice items, uniquely active trip allocations and immutable audit records. Existing headers remain non-trip-backed.
- Durable accepted-trip records are required in Phase 2 to block legacy user/driver/request cascades even before an invoice item exists.
- Decimal casts and fixed-point money; audited permissioned/idempotent membership assignment/removal; explicit removal before transfers.
- Account/company deletion protections; model relations; private billing snapshots hidden from generic serialization.
- Corrected CurrencyConversion's constructor to call Eloquent's parent constructor, ensuring model/trait boot, casts and hidden-field initialization actually run.

Validation includes SQLite regressions and a dedicated `phpunit.corporate-mysql.xml` profile for MySQL 8.4 upgrade/retry/ownership/cascade tests. The profile exclusively targets `lala_corporate_schema_test`; its fixture refuses any other non-SQLite database and removes only its own fixture tables. Supply DB_PASSWORD through the environment. This is not a production test configuration.

## Phase 2 — Payer attribution and dispatch

- New corporate bookings stay disabled by default via `CORPORATE_BOOKINGS_ENABLED`; management remains accessible independently of this pilot guard.
- Server-derived affiliation defaults to corporate billing; explicit personal intent remains supported without adding a passenger mode switch. Reject forged account IDs, contradictory intent, personal wallets, foreign currency, pooled vehicles and parcel services.
- Eloquent creation hooks carry private immutable bill-to snapshots through schedules, legacy/realtime requests, retries and accepted trips. Stored personal bookings cannot inherit a later affiliation. Trip operating company remains distinct from bill-to account ownership.
- Durable trip records are inserted on acceptance; legacy/direct acceptance transactions and rider/group locks prevent competing offers from assigning a second active trip. Revoked membership blocks pending acceptance without personal fallback; accepted-trip updates do not require the new-booking feature.
- Manual Corporate intent requires an existing eligible rider before related creation, with country-scoped phone lookup and revalidation for drafts/templates/return/recurring schedules. Unsupported card/wallet/invoice methods remain unavailable.
- Due ineligible corporate schedules retain Pending status but pause assignment with timestamp/reason; notify once, continue other schedules, and revalidate before resumption. Rider schedule editing is ownership-scoped.
- Legacy pushes and realtime offers/status expose a safe billing summary rather than finance contacts/address. Realtime transaction notifications are emitted after commit; invalid corporate retries stop instead of falling back.

Verification: dedicated booking regressions on SQLite and MySQL, complete existing PHPUnit suite, additive full MySQL migration, PHP/Blade syntax and isolated route cache. Detailed final counts are recorded in the corresponding commit verification output.

## Phase 3a — Finalization and collection safeguards

- Account-first transactional fare finalization freezes amounts/currency and driver earnings once, links one retained pending payout record, and records Deferred/unbilled completion without fake personal gateway transactions. Conflicting legacy payouts require explicit reconciliation rather than destructive cleanup.
- Corporate calculation bypasses personal promotions, wallet deductions, referral consumption and cash-collection debt. Accepted billing currency remains fixed after affiliation, configuration or rider currency changes.
- Realtime completion rolls back if accounting fails; retry finalizes before releasing the driver or broadcasting completion. Legacy completion validates the assigned driver and preserves finalized physical/financial metadata on retries.
- Cash, main payment service and gateway-specific entry/callback paths reject corporate collection before charging or creating payment transactions. Gateway trip requests bind authenticated identity and explicit trip; existing payment forms forward these identifiers.
- Driver checkout distinguishes earned, pending and withdrawable funds; payout scopes exclude deferred obligations. Finalized corporate trips allow rating and cannot be reopened by web rating. Rider web receipts/details are ownership-scoped.
- Shared safe finance fixtures exercise the real fare engine without personal wallet/promotion/debt tables, financial immutability, duplicate payout recovery, rejected gateways and atomic completion/retry.

Verification: full PHPUnit suite 87 tests / 353 assertions; PHP syntax, isolated route cache, compiled Blade syntax, additive full MySQL migration and dedicated corporate MySQL regressions. Invoice generation, lifecycle, delivery and settlement remain Phase 3b; this milestone is not end-to-end finance completion.

## Phase 3b — Trip-backed invoices and company settlement

- Closed half-open weekly/monthly/quarterly periods in the account timezone, stored as UTC; reject non-cycle dates and open periods. UTC casts preserve accounting instants even when legacy trip code changes PHP's timezone; legacy fare timestamps retain their existing semantics.
- Immutable trip-backed headers/items, uniquely active allocations, atomic generation, harmless dry-runs and supplementary revisions for late charges. Archived accounts retain invoicing of existing liabilities.
- Authorized Draft → Sent issuance with durable recipient/channel outbox; exact full amount/currency/reference/date settlement records; atomic paid trip eligibility. Idempotent retries and contradictory/legacy-header actions rejected.
- Unpaid void retains headers/items/audit, releases only active allocations and restores Deferred/unbilled charges for re-invoicing. Paid invoices cannot be voided; in-flight delivery is protected.
- Real saved-data PDF rendering and email attachments, retained retry attempts and delivery evidence. No per-trip personal gateway transaction is fabricated.
- Recovery, generation and delivery Artisan commands and UTC schedules with overlap protection; single-server locks when a shared supported cache is configured. Empty reconciliation does not resolve fare dependencies unnecessarily.

Verification: general PHPUnit 103 tests / 444 assertions, with two MySQL-only concurrency tests exercised separately; the MySQL profile passed all 43 tests / 249 assertions, including simultaneous generation and settlement workers, recovery and zero-fare settlement. Additive full-schema MySQL migration, PHP/compiled Blade syntax, isolated route cache and empty-database command execution passed.

SMTP delivery is at-least-once when a worker fails after the transport accepts a message; retries use a stable message identity and never duplicate invoice issuance. Sent means issued for delivery; the outbox separately records pending/failed/delivered evidence. Live SMTP and production host cron activation belong to rollout verification, not these local tests.

## Phase 4 — Admin/company management

- Implemented account create/update/archive service, audited changes and billing-setting protection after accepted trips.
- Implemented account exposure calculations across unpaid periods, with warning-only credit limits.
- Added controller actions and routes for account details/edit/archive, membership roles, trip details, invoice generation/issue/settlement/void and authorized PDF downloads.
- Retained account servicing remains accessible when new corporate bookings are disabled.
- Completed translated account forms, account/member/trip/invoice detail pages, lifecycle forms and paginated history using the existing admin shell and square-corner tokens. Unreconciled legacy headers cannot enter the trip-backed workflow; issuance and delivery evidence are separate.
- Updated sidebar/search capabilities and direct detail destinations so retained records remain reachable when new corporate features are disabled. Inactive currencies remain selectable for servicing existing agreements, not new accounts.
- Closed cross-owner draft/template overwrites. Operation-team permission lookup is request-local, bound to the authenticated tenant and fails closed for missing/foreign/inactive staff or roles; mobile membership does not confer web permissions.

Verification: full PHPUnit 120 tests / 565 assertions (two MySQL-only tests skipped there); the full isolated MySQL profile passed 55 tests / 344 assertions, followed by the updated management subset passing 13 tests / 102 assertions. Real web routes exercise creation/edit/archive, membership/tenant permissions, saved-charge generation, issuance, PDF, settlement and unpaid void/re-invoice. PHP syntax, 359 compiled Blade files, isolated route cache and full-schema migration check passed. Browser QA passed 24 combinations across 1440/1024/390 widths and light/dark themes, with no viewport overflow, untranslated labels, missing field labels or JavaScript errors; square card/control corners were asserted. Desktop/mobile screenshots were visually inspected. The local preview uses isolated fixtures, not production data. No frontend bundle is involved in these Blade-only screens; live SMTP, production deployment and mobile integration are still remaining work.

## Phase 5a — JWT API and legacy compatibility

- Added verified-JWT corporate profile, role-scoped dashboard/trips, manager membership views and permissioned invoice/detail/PDF endpoints with explicit response fields and private cache headers. Employee summaries are own-trip only; manager invoice access requires `CORPORATE_MANAGER_FINANCE_ACCESS` (false by default); finance cannot list membership details.
- Dashboard metrics use finalized charges in explicit account-timezone periods, distinguish Draft from issued unpaid invoices and reject mixed saved currencies. Credit is warning-only and restricted to manager/finance; null membership is a normal profile result. Archived agreements retain servicing; inactive currencies block new corporate bookings.
- Real JWT tests cover employee/manager/finance scope, foreign trips/invoices, PDF authorization, archive servicing, immediate role revocation and validation. They exposed and fixed a legacy authentication comparison that unnecessarily serialized the entire user model.
- Legacy trip details/receipts/history and schedule payloads preserve safe payer summaries. Finalized corporate amounts, payout earnings and currency come from saved accounting data, not the viewer's current currency. No bill-to contact or internal financial snapshot is serialized; corporate receipts do not require personal PayPal credentials.
- Added participant/assigned-driver checks before legacy detail, arrival, start, cancellation and map-upload actions. Account-first transaction locks reject finalized-charge reopening and preserve arrival/start timestamps on retries. Removed full trip-response logging and history's request-leaking global append toggle. Personal cash history retains amount/currency formatting and valid map URLs.

Verification: general PHPUnit passed 131 tests / 729 assertions (two MySQL-only cases skipped there); the dedicated isolated MySQL profile passed all 67 tests / 515 assertions, including 11 real-JWT API/legacy-contract tests. PHP syntax, 364 compiled Blade files, isolated route cache, `git diff --check` and full-schema migration check passed. No frontend/mobile build or device verification is claimed for this backend-only checkpoint.

Execution resumed on 2026-10-08. This verified checkpoint does not establish completion of the overall implementation.

## Phase 5b — Transport and reconnect contracts

- Alternate persisted-trip offer acceptance now revalidates the payer and locks account/rider before the offer/trip. It excludes only its own searching trip from active-trip checks; another active trip or revoked membership blocks acceptance without changing the offer.
- Both assignment paths, cancellation, status and completion events carry safe billing/payment context. Completion events include saved fare, payout and currency; Deferred is never normalized to paid. Searching reconnect responses preserve request identity, nullable trip ID and Pending payment status.
- Real-JWT reconnect/detail/completion tests verify saved employer identity after revocation/rename, private field exclusion, foreign access rejection, completed trips no longer counted as active, driver release and idempotent completion/events/payout records. Existing booking tests continue covering schedule/retry/context propagation and personal intent compatibility.

Verification: full PHPUnit passed 135 tests / 784 assertions (two MySQL-only cases skipped); the dedicated MySQL profile passed all 71 tests / 570 assertions. Full PHP syntax, isolated route cache, diff whitespace check and full-schema MySQL migration check passed. Mobile end-to-end transport exercises, including real scheduled/direct/manual entry points and push/reconnect behaviour, remain part of Phase 8; these are not established by model/contract tests alone.

## Phase 7 — Driver app

Implemented saved employer chips on offers, pickup/arrived/waiting/on-trip, history and scheduled/detail pages, plus a prototype-language corporate completion screen. The real provider preserves Deferred separately from paid, bypasses/rejects corporate cash collection, releases the local trip on Done, and displays frozen currency/decimal earnings separately from withdrawable funds. Loading/retry and late-response guards protect receipts; historical currencies are not summed together or written over driver preferences. Shared semantic colors/radii, Lucide and centrally bundled Inter are reused.

Verification: 12 Flutter tests passed, including provider/transport regressions and 375/390/430-width 1×/2× native receipt checks; analyzer and Android debug build are checked before commit. Native golden inspection is recorded in Driver/docs/corporate-implementation-progress.md. Live device/push/GPS and staging finance release checks are not claimed.

## Customer checkpoints

### Phase 6a — Customer affiliation/dashboard checkpoint

Customer adds typed corporate contracts, protected session-bound caches, real company Profile identity, role-aware Overview/Users/Trips/Invoices, saved details and authenticated PDF sharing. Employee tabs/data remain own-only, managers/finance follow server capabilities, and revocation/account change clears privileged cached responses. Prototype tokens, Inter/Lucide and English/Spanish translations are reused.

Verification: Customer full Flutter suite 16 tests passed; full analyzer found no issues; Android debug APK built; golden inspected with real fonts; 375/390/430 widths and 1×/2× text checks passed. Native/device/backend-integrated testing is not established by this checkpoint. Customer-specific evidence is tracked in its `docs/corporate-implementation-progress.md` and `docs/corporate-design-qa.md`.

### Phase 6b — Customer booking and Deferred receipt checkpoint

Customer now revalidates explicit payer intent on ordinary/scheduled/legacy bookings, excludes corporate wallet/promo and unsupported parcel requests, replaces confirmation's personal method selector with actual bill-to/availability/credit information, preserves billing through nested/serialized transports and reconnect, and supports Deferred receipt/rating/close/rebook without personal checkout or fake payment. Saved currency overrides current session currency; receipt/PDF/history use the saved payer. Personal request methods remain unchanged.

Verification: Customer full suite 28 tests, clean analyzer, Android debug APK and whitespace/translation checks. Native receipt tests cover 375/390/430 at 1×/2× text and real-controller transport fixtures cover completion/reconnect/rating. These isolated fixtures do not establish live mobile/backend/device integration; that remains Phase 8.

## Remaining phases

### Phase 8a — Backend/mobile contract verification (2026-10-08)

Real JWT and legacy endpoint output is exported for both Flutter apps after finalized Deferred completion, issued invoice, authorized PDF and idempotent settlement. It verifies earned versus withdrawable payouts, immutable currency/company attribution, role permissions and driver private-field exclusion. Test-only deterministic invoice IDs make the artifact reproducible; production numbering is unchanged.

Customer passes 33 tests, Driver 15, both analyzers are clean and Android debug APKs build. Account-change/late-response and duplicate schedule protections accompany actual API/repository/provider contract consumption. Backend general suite passes 136 tests / 822 assertions (two MySQL-only cases skipped); isolated MySQL passes all 72 tests / 608 assertions. Live server/device/GPS/push/SMTP acceptance is not inferred from fixtures.

8. Live staging/device end-to-end verification remains separate from completed contract/regression checks.
### Phase 9a — Controlled release artifacts and read-only preflight (2026-10-08)

- CI now gates builds on the complete isolated MySQL profile, reproducible mobile contract, PHP/compiled Blade lint and cached routes. Deployment bundles identify the tested Git commit and immutable GHCR digest instead of promoting `latest`.
- App alone refreshes the shared code, applies additive migrations and rebuilds config/routes/views. Queue, Reverb and the new scheduler wait for app health and reject a mismatched shared-code build. Startup is bounded; existing readers stop before code replacement.
- Read-only release checks verify schema, restrictive MySQL finance FKs, retained invoice ownership/allocation, finalized payer/currency/amount and Deferred/paid consistency. Runtime checks require shared Redis cache/queue and configured real mail. Checks never repair records or send mail.
- A tracked file manifest blocks obsolete code pending explicit recoverable audit; it never deletes uploads or financial evidence. Registry builds exclude operator credentials, generated receipts/backups and runtime storage.
- Backup/restore evidence is required before release. The runbook preserves keys, volume/project identity, accepted obligations and financial history; it distinguishes internal Reverb settings from public client TLS and requires a single scheduler source.

Verification: general PHPUnit **145 tests / 854 assertions**, with two MySQL-only cases skipped there; dedicated MySQL **78 tests / 630 assertions**, all passing. PHP syntax, 364 compiled Blade templates, isolated cached routes, shell syntax, workflow YAML, Compose image/health/dependency configuration and deterministic contract regeneration passed. Composer validates with its existing JWT development-version warning. Docker build passed after an intermittent Docker Hub timeout retry. An isolated full-schema MySQL container migrated successfully; repeat startup reported nothing to migrate and rebuilt all caches. The current release-check passes against that database through app and worker entrypoints; an empty/mismatched worker volume is rejected. These tests do not execute production scheduler tasks or send live invoices.

Read-only production inspection: deployed image revision `4b7501d816e3112d0299b393638b2b26f38dbb8c`, digest `sha256:7329f47c8cd071d90e1718f1597dc1129f67101a0bc472395c0a6fd5b8f3a24a`; all five corporate migrations were already applied before this inspection. The six taxi services still use `latest`, cache is `file`, queue is Redis, mail is SMTP and new corporate bookings are disabled. No taxi scheduler service or matching schedule/corporate entry was found in root crontab or the checked system/aaPanel cron directories. No cached config/routes were found. The deployment backup directory contained an older page-table SQL backup, not demonstrated current DB/upload backup and restore evidence. No production configuration/data/service changes were made.

Live gates remain: current full backup and verified isolated restore; approved staging identities/finance workflow and controlled SMTP recipient; observed scheduler/queue/Reverb/GPS/push executions; authenticated website/personal/mobile regressions. Driver ARM64 APK installation succeeded without removing user data, but the device was in personal use and then disconnected, so device acceptance is not passed. Customer's latest 33-test suite and Driver analyzer were rerun successfully during final review. No pilot enablement or production finance acceptance is claimed.

9. Deployment artifacts and local release checks are implemented; environment-dependent rollout gates remain open.

### Final review follow-ups — Driver sessions and CI fixture origin

Driver follow-up `8c08d888c87126dbcf2dcb689f7a405d2c60d563` is pushed: auth-generation guards now cover optimistic availability and accept/arrive/start/complete, discard late GPS fixes and block duplicate trip actions. Session change/offline stops foreground tracking and discards pending location uploads; old failed batches cannot be requeued into a new tracking session. Driver passes **24 tests**, clean analyzer and ARM64 debug APK build. Customer's **33 tests** and analyzer pass again; its existing universal/ARM64 debug builds remain verified.

The first Phase 9 CI run passed general/MySQL tests and lint but correctly blocked image/package publication on a fixture difference: the runner's `APP_URL=http://localhost` changed absolute links from the checked-in `127.0.0.1` contract. Both PHPUnit profiles now force the same isolated test origin before application bootstrap, including the legacy console `SITE_URL` constant. Actual response data/production configuration and the three fixture copies are unchanged. Regeneration under a conflicting localhost environment now matches byte-for-byte. Fix `b42f278` is pushed, and CI run 37756585735 passed all three jobs (tests, image build/publication and deployment-bundle packaging). Live release gates above remain open.

No production migrations, deployment, payments or financial data changes have been performed. Corporate booking remains disabled; these backend milestones do not yet establish complete web/mobile end-to-end support.

### Phase 8c — Guarded Kenyan acceptance identity provisioning (2026-10-08)

Added `corporate:provision-acceptance` for the exact non-production MySQL database `lala_corporate_acceptance_test`. It creates two fictional operators/employers, six member/manager/finance riders, one personal rider, two QA drivers/vehicles and a least-privilege web administrator. Credentials are operator-supplied, hashed and never printed; no financial records, JWTs or fake approval documents are seeded. Existing account/membership services provide the actual permission checks and audits. Repeated provisioning preserves credentials/audits; configuration or identity conflicts fail transactionally instead of overwriting data.

Verification: **5 safety tests / 9 assertions**, including rejection before any query for production/wrong database/SQLite/missing credentials; **2 full-schema MySQL integration tests / 23 assertions**, including repeatability, real permissions, unchanged credentials, no financial seeding and complete rollback of configuration drift. The full-schema test applies normal migrations and rolls back test insertions without dropping tables. CI now includes this independent database/profile. PHP syntax and whitespace checks pass. The identity matrix and safety procedure are documented in `deploy/CORPORATE_ACCEPTANCE.md`. Live runtime provisioning and device/SMTP acceptance remain separate next checks.

### Phase 8d — Real local runtime and authenticated HTTP acceptance (2026-10-08)

Provisioning is now executed in an independent local Docker stack. Two fictional employers/operators, nine mobile identities and three web identities are present; no trips/invoices/settlements were seeded. nginx/FPM, MySQL, Redis, Mailpit, queue, Reverb and one scheduler run on isolated volumes/network, with only loopback HTTP/WebSocket/inbox ports. Local images extend the already tested runtime with an enforced matching dependency lock; production image builds are unchanged. Secrets are randomly operator-generated, retained in the isolated runtime and never committed or printed.

Observed **75 HTTP checks passed** through nginx/FPM: real OTP issuance/verification and signed JWTs for all nine users, private corporate responses, employee/manager/finance scopes and restrictions, personal affiliation absence, driver finance denial, and real CSRF/password/session login for admin and both company owners. Authenticated corporate management and denied company access to admin tenant entry pass. Landing HTTP returns 200.

These checks exposed and fixed bootstrap defects: optional Facebook credentials no longer break ordinary user/company pages or skip unrelated configuration; missing DB email settings preserve environment SMTP; API requests retain configured Redis cache; payment currency uses its named setting, not row order. Request bootstrap/realtime entry no longer logs raw query strings/POST bodies. Runtime preflight now pings the configured Redis client rather than accepting configuration names alone; the staging stack explicitly uses installed Predis. New bootstrap regressions pass. The test schema fixture recovers from interrupted runs only in its explicitly guarded test database.

Observed scheduler logs show scheduled-ride scanning, stale-driver cleanup, notification processing and corporate outbox delivery executing successfully; Redis queue processed the cleanup job and Reverb started. Full MySQL regressions passed again: **78 tests / 630 assertions** before the added Redis-client regression. Docker Hub timeouts and an externally stopped local test DB interrupted initial attempts; the DB was restarted and the full run passed. The Android device has reconnected, but native acceptance and finance/SMTP observations remain pending. The current local image includes a working-tree marker, not a final production release SHA; final acceptance must be repeated on the committed image.

Updated verification: general PHPUnit **155 tests / 881 assertions**, with two MySQL-only skips; updated MySQL release subset **7 tests / 24 assertions**; independent full-schema provisioning **2 tests / 23 assertions**. Driver staging ARM64 debug APK builds successfully using isolated API/Reverb defines; production defaults are unchanged. Bootstrap tests include ordinary company/user controller construction without optional social credentials and API Redis configuration. These checks do not yet establish native acceptance or invoice SMTP delivery.
