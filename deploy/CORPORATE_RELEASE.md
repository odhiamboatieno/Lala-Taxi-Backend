# Controlled release and corporate pilot

CI tests PHP/Blade, isolated MySQL migrations/concurrency/retention and the reproducible mobile contract before building an image. It packages the runbook, Compose, scripts, verification log and `release-manifest.json`; it does **not** SSH deploy or enable corporate bookings.

## Release gates

1. Record current Git SHA/image digest, migration status, host services and cron. Inventory the DB, `app_code`, `app_storage`, `app_trip_files`, credentials and uploads in `public/images`. Never reset an existing volume or import a dump over live data. Back them up and verify a full restore in isolated staging before releasing.
2. Compare old/new Git releases and image `.src-files` manifests. Refresh is additive and preserves uploads; the audit refuses obsolete tracked code instead of deleting blindly. Review listed paths, archive exact obsolete code files recoverably outside the served app, and rerun. Never move/delete storage, credentials, image uploads or whole directories. The first manifest deployment additionally requires comparing the previous Git release.
3. Copy the bundle into the **existing** Compose project directory; retain the project name/data volumes. Populate `.env` through credential management. Preserve existing APP_KEY, JWT/realtime keys and DB secrets: replacing them invalidates sessions/encrypted data. Previously exposed credentials must be rotated through the approved process, not written into docs.
4. Set `LALATAXI_IMAGE` to the tested GHCR sha256 digest from the manifest. App, queue, Reverb and scheduler must share it; never promote `latest`. Set `CACHE_DRIVER=redis` (this codebase reads it, not just CACHE_STORE), `QUEUE_CONNECTION=redis`, `APP_DEBUG=false` and configured SMTP/sender. Keep `CORPORATE_BOOKINGS_ENABLED=false` and `CORPORATE_MANAGER_FINANCE_ACCESS=false` initially.
5. Run `sh preflight.sh --config-only` without booting services. Ensure **one** scheduler source: included scheduler service or approved host cron, never both. Pause host cron during migration/code refresh. Existing scheduled tasks include deliveries, reconciliation, backups and personal workflows; review effects on staging before starting them.
6. Supply real backup/restore evidence through your operator environment and run `sh release.sh`. The script requires `BACKUP_REFERENCE` and `RESTORE_EVIDENCE`; these must identify actual verified artifacts, not example values.

The script pulls before downtime, stops nginx/all readers, starts the app writer alone, applies additive migrations, refreshes config/routes/views and checks schema/runtime before starting nginx/queue/Reverb/scheduler. Healthy readers verify shared-code build identity. It never seeds accounts, settles invoices, voids charges or enables new bookings. Failure stops the release; investigate rather than bypassing a gate.

## Acceptance evidence

Record SHA/digest, `docker compose ps`, migration status, cached routes and `php artisan corporate:release-check`. This read-only command validates schema/ownership, finalized Deferred/paid statuses, restrictive MySQL finance FKs, shared cache/queue and non-test mail. `--schema-only` is for isolated tests; `--allow-bookings` validates an already authorized pilot without changing the flag. Neither repairs financial data or overrides migrations.

On restored staging, exercise authenticated employee/manager/finance and company/admin contexts; direct/scheduled/realtime bookings, retries, repeated finalization/settlement, void/re-invoice, revocation/tenant tampering and disabled-new-bookings with accepted obligations. Use test identities/money. Install compatible Customer/Driver builds and verify actual sockets/push/GPS, Deferred receipt, rating/rebook and personal cash regression on devices. Native goldens/contract fixtures alone do not prove these workflows.

Inspect `php artisan schedule:list`, scheduler/queue/Reverb logs and observed execution of all three corporate commands. Observe outbox pending/failed/delivered with a controlled SMTP recipient; Sent means issued, not delivered. Never invoke delivery to real recipients just as a health check. Only after finance/device acceptance may authorized operators enable scoped corporate bookings.

TLS/public frontend/onboarding must also pass regression checks. `REVERB_HOST=reverb`, port 8080 and scheme http are internal broadcasting settings; clients use the public TLS reverse proxy. Do not substitute client settings into internal settings blindly. Restrict service ports through existing ingress/security policy.

## Recovery

Stop **new** corporate bookings on pilot failure while preserving accepted trip servicing, receipts, reconciliation and settlement. Use the previous tested image digest only after confirming code/schema compatibility and backing up current state; stop readers before shared-code changes. Financial columns/items/settlements/audits remain. Never use destructive migration rollback, detach members, void invoices or erase volumes as deployment rollback. Backup restore requires explicitly approved recovery reconciled with every transaction since the backup.

Production backup/restore, SMTP, observed cron and device evidence remain environment-dependent gates. This bundle does not certify they occurred.
