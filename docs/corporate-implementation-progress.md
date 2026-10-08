# Corporate implementation progress

Source specification: workspace-root `CORPORATE_ACCOUNTS_IMPLEMENTATION.md` (corrected 2026-10-07).

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

## Remaining phases

5. Rider/driver API authorization and contracts.
6. Customer affiliation and role-aware dashboard.
7. Driver badges, no-collection completion and history.
8. End-to-end integration and regression verification.
9. Deployment artifacts, scheduler, disabled pilot and rollout validation.

No production migrations, deployment, payments or financial data changes have been performed. Corporate booking remains disabled; these backend milestones do not yet establish complete web/mobile end-to-end support.
