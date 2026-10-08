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
