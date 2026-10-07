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

## Remaining phases

1. Additive corporate schema, fixed-point amounts, relations/retention.
2. Authorized payer attribution across booking, schedules, dispatch and retries.
3. Idempotent finalization, Deferred compatibility, invoices/settlement.
4. Complete admin/company management and delivery/PDF.
5. Rider/driver API authorization and contracts.
6. Customer affiliation and role-aware dashboard.
7. Driver badges, no-collection completion and history.
8. End-to-end integration and regression verification.
9. Deployment artifacts, scheduler, disabled pilot and rollout validation.

No production migrations, deployment, payments or financial data changes have been performed. Completion of Phase 0 does not enable corporate booking or establish end-to-end support.
