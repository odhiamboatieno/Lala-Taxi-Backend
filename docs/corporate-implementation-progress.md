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

## Remaining phases

3b. Invoices, period generation, delivery, settlement and scheduler.
4. Complete admin/company management and delivery/PDF.
5. Rider/driver API authorization and contracts.
6. Customer affiliation and role-aware dashboard.
7. Driver badges, no-collection completion and history.
8. End-to-end integration and regression verification.
9. Deployment artifacts, scheduler, disabled pilot and rollout validation.

No production migrations, deployment, payments or financial data changes have been performed. Corporate booking remains disabled; completion of Phase 2 does not establish end-to-end finance/client support.
