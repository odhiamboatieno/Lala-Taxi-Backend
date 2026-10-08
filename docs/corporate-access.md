# Corporate accounts: access and login

## Platform admin

Sign in at `/admin/login`. In the sidebar open **Finance and accounts → Accounts / invoicing** (`/admin/saas-dispatch/accounts`), choose the owning fleet/operator company, then open an employer corporate account.

The account detail page contains **Members**, role selection, eligible rider search/attachment, saved trip charges and invoices. Roles are Employee (`member`), Manager (`manager`) and Finance (`finance`). Only eligible active Rider accounts can be attached; changing employers requires removing the existing affiliation first. Archived accounts retain historical trips/invoices and do not accept new membership or bookings.

Platform administrators require `manage_company_accounts_invoicing`, and corporate servicing must be available. If the menu is absent, verify these permissions/capabilities and the deployed release. Do not enable new corporate bookings merely to display retained account records. The tenant entry is `/admin/companies/{company}/corporate-accounts`; it is not an employer-manager login.

## Fleet/operator web staff

Use the existing `/signin_company` login with company/operation-team credentials. Authorized staff open `/company/saas-dispatch/accounts`. Account creation, membership assignment and invoicing require operation-team permissions and the operator's corporate capability. A mobile Manager role does not grant these web permissions.

## Employer managers and finance

There is **no separate employer-manager web portal/login in the implemented scope**. An employer manager uses the Customer app's ordinary **phone number + OTP** sign-in. Admin/operator staff first attach that Rider identity to the correct employer account and assign the Manager role. Profile displays the employer account; tapping it opens the corporate dashboard. There is no separate Corporate mode or password login.

- Employee: own corporate trips and metrics.
- Manager: permitted account-wide metrics/trips and member list.
- Finance: permitted account-wide metrics/trips, invoices and authenticated PDFs; no member list.
- Manager invoice access is disabled by default (`CORPORATE_MANAGER_FINANCE_ACCESS=false`).

Roles are server-managed. Knowing an account ID or being a billing contact does not grant access. Employer billing contacts are invoice recipients, not automatically login identities. Missing/revoked membership must not expose account-wide data or silently turn an intended corporate booking into personal billing.

## Acceptance versus production

Fictional Kenyan identities are created only in the guarded local acceptance database. They are not production accounts, and their demo OTP does not establish a production login policy. See [acceptance runbook](../deploy/CORPORATE_ACCEPTANCE.md). Production promotion and booking enablement remain controlled release gates.
