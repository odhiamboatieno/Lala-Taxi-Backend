# Isolated corporate acceptance identities

This environment is separate from production and contains fictional Kenyan identities and employers. It must not use a production database, production messaging credentials, production uploads or production financial records.

## Provisioning

Create a dedicated MySQL database named **`lala_corporate_acceptance_test`**, apply the normal additive migrations, and configure the application environment as `staging` or `local`. Set `LALATAXI_ACCEPTANCE_PASSWORD` through the operator's secret store to a random value of at least 16 characters. Do not pass a password as a command argument or commit it.

Run:

```sh
php artisan corporate:provision-acceptance
```

The command rejects production, other databases, SQLite and missing/short passwords before any data query. It creates identities/configuration transactionally, uses the existing account and membership services for authorization/audits, and refuses conflicting configuration or identity data. Repetition does not reset credentials, create duplicate identities or duplicate membership audits. It does not seed trips, invoices, settlements, payment transactions or fake verification documents. Vehicles are explicitly labeled QA, not registered road vehicles.

## Identity matrix

All mobile numbers below are **test inputs only**. Do not send SMS to them. Configure `OTP_DEMO_MODE=true` and `OTP_DEMO_CODE=123456` only in the isolated acceptance environment. Mobile login must still call the real OTP issuance and verification endpoints. No JWT is seeded.

| Login | Name | Role | Employer / operator |
|---|---|---|---|
| `qa_njeri` | Njeri QA Administrator | Web administrator | Both fictional operators |
| `operator1@acceptance.lalataxi.test` | Lala QA Platform | Company owner | Mwangaza Workspaces Kenya |
| `operator2@acceptance.lalataxi.test` | Rift Valley Fleet QA | Company owner | Tawi Coast Supplies |
| `+254 700000110` | Njeri Mwangi | Member | Mwangaza Workspaces Kenya |
| `+254 700000111` | Amina Wanjiku | Manager | Mwangaza Workspaces Kenya |
| `+254 700000112` | Otieno Omondi | Finance | Mwangaza Workspaces Kenya |
| `+254 700000120` | Fatuma Ali | Member | Tawi Coast Supplies |
| `+254 700000121` | Baraka Njoroge | Manager | Tawi Coast Supplies |
| `+254 700000122` | Wanjiru Kiptoo | Finance | Tawi Coast Supplies |
| `+254 700000130` | Kamau Mutua | Personal rider | No corporate affiliation |
| `+254 700000140` | David Kiptoo | Driver | Lala QA Platform |
| `+254 700000141` | Brian Onyango | Driver | Rift Valley Fleet QA |

The web logins use the operator-supplied password; the command never prints it. Employers and addresses are fictional. Their `.test` finance recipients must be captured by a local SMTP inbox such as Mailpit, with no relay enabled. This verifies the real SMTP transport/PDF attachment without emailing a real company. It does not prove external production mailbox delivery.

Corporate booking can be enabled **only in this isolated environment** for acceptance. Keep the production flag disabled. The web administrator has the corporate-management permission, not blanket platform privileges. Manager mobile finance access remains off by default.

## Automated verification

The general PHPUnit suite tests the pre-query safety guards. A separate profile uses the complete core migration schema and verifies real provisioning, hashed credentials, least-privilege permissions, audit/idempotency, unaffiliated personal users and atomic refusal of drift:

```sh
vendor/bin/phpunit -c phpunit.corporate-acceptance.xml --no-coverage
```

Supply `DB_PASSWORD` through the environment. The profile connects to local MySQL port 33317 and refuses another database. It applies migrations and rolls back each test's inserted data; it does not drop existing tables or delete financial evidence. CI creates this isolated database independently of the reduced-schema concurrency test database.

Account provisioning alone does not pass acceptance. Record observed OTP/JWT authentication, permitted and forbidden role/tenant requests, actual booking/completion, Deferred receipts, invoice/PDF delivery, settlement/retry, personal-flow compatibility and queue/scheduler/Reverb/device GPS/push evidence in the implementation progress log. Follow `CORPORATE_RELEASE.md` for production backup/restore and promotion gates.

## Local acceptance stack

`acceptance-compose.yml` is a separate Compose project with independent database, Redis, code, storage and SMTP-inbox volumes. Only HTTP (8095), Reverb (8096) and the Mailpit inbox (8026) are exposed, all bound to loopback. Database/Redis/SMTP submission ports are not public. It uses real nginx/FPM, Redis queue, Reverb and one scheduler. Demo OTP and corporate booking are enabled only in this stack; this is not the production deployment configuration.

Provide `LALATAXI_ACCEPTANCE_APP_KEY`, `LALATAXI_ACCEPTANCE_JWT_SECRET`, `LALATAXI_ACCEPTANCE_PASSWORD`, `LALATAXI_ACCEPTANCE_DB_PASSWORD`, a separate `LALATAXI_ACCEPTANCE_ROOT_PASSWORD`, and `LALATAXI_ACCEPTANCE_REVERB_SECRET` via the operator's environment/secret store. Never print a resolved Compose configuration containing them. Keep these values stable when recreating the stack.

Set `LALATAXI_ACCEPTANCE_RELEASE_SHA` to the revision under test and `LALATAXI_ACCEPTANCE_BASE_IMAGE` to a previously tested production runtime image. The acceptance Dockerfile verifies that `composer.lock` is identical to that image before reusing its PHP extensions/vendor packages. If runtime dependencies changed, first build a new base with the standard production Dockerfile. Acceptance images are local artifacts, not production releases.

```sh
docker compose -f deploy/acceptance-compose.yml config --quiet
docker compose -f deploy/acceptance-compose.yml up --build -d --wait app nginx
docker compose -f deploy/acceptance-compose.yml exec app php artisan corporate:provision-acceptance
docker compose -f deploy/acceptance-compose.yml up -d --wait queue reverb scheduler
```

The HTTP verification tool is deliberately outside production images. Copy it into the isolated code volume and run it there:

```sh
docker compose -f deploy/acceptance-compose.yml exec app mkdir -p /var/www/html/deploy
docker compose -f deploy/acceptance-compose.yml cp deploy/acceptance-http-check.php app:/var/www/html/deploy/acceptance-http-check.php
docker compose -f deploy/acceptance-compose.yml exec app php deploy/acceptance-http-check.php
```

It verifies the exact database/environment and local demo OTP/non-relaying Mailpit configuration, then exercises real HTTP OTP/JWT and CSRF/password/session logins for all nine mobile identities and all three web identities. It checks role scopes/private responses and denied admin tenant entry without printing credentials/JWTs or performing finance settlement. Repetition issues fresh OTPs but does not seed financial records.

### Trips and retained finance evidence

Copy `deploy/acceptance-trip-check.php` and `deploy/acceptance-finance-check.php` into the same isolated `deploy` directory. Run the trip check first, then the finance check. These are stateful acceptance tools, not fixture seeders or production commands: trips are booked/accepted/started/GPS-updated/completed through real HTTP APIs. Corporate Deferred fare immutability, driver release, unaffiliated cash billing and explicit personal billing are checked. Interrupted trip checks resume only the same QA rider/assigned driver; completed charges and cash-fee evidence are retained, never reset. Repeating a completed trip check creates additional real QA trips.

The finance check uses the saved actual corporate charges and the existing account/invoice services. Only its guarded CLI process advances the Carbon clock to the next month so the period closes. It first verifies that the real-time open period is rejected; it does not alter server/device clocks or trip timestamps and is **controlled-clock acceptance**, not naturally elapsed month-end evidence. It exercises retained void/re-invoice revisions, cross-owner denial, issuance/idempotent delivery, real PDF generation and SMTP to the non-relaying Mailpit inbox. Finance/driver JWT responses are checked before and after an exact fictional company settlement; settlement retries must not duplicate records. No actual funds move and no personal gateway transaction is fabricated. Paid invoices and original void items remain available for inspection.

The tools honor actual API rate limits and bounded `Retry-After` responses; they do not disable middleware. The provisioning command reuses normal DriverScore settings only when that configuration table is empty; it never seeds scores, compliance approvals or scoring events.

Device builds can use the existing `LALATAXI_API_BASE_URL` and `LALATAXI_REVERB_*` Dart defines with adb reverse for ports 8095/8096. Do not change production defaults or uninstall personal applications to free device storage. A staged build, service health check or passing HTTP script is not proof of native GPS/push or external production SMTP delivery.

### Observed private sockets

From the repository root, `node deploy/acceptance-socket-check.mjs` (Node 22+) verifies the running isolated Docker environment before authenticating the fictional Driver/Rider identities. It connects without Origin as native clients do, rejects the Rider's driver-channel subscription, subscribes with the Driver's JWT, and observes the actual queued availability event after a real offline API call. It closes and reconnects and repeats subscription/delivery; tokens never leave process memory or appear in output. The driver is left offline. Run only when no device trip is active. These are real WebSocket checks, not proof that the native plugin, push or GPS works. The loopback-only acceptance server permits absent Origin; private-channel JWT/participant permissions remain enforced. Production origin configuration is unchanged.

See [corporate access and login](../docs/corporate-access.md) for the distinction between fleet operator web accounts and employer member/manager/finance mobile identities.
