# Corporate accounts: access and login

## Platform admin

Sign in at `/admin/login`. In the sidebar open **Business travel → Corporate accounts** (`/admin/corporate-accounts`). The directory lists employers across all operators, with search, operator/status filters, active employee counts and activated portal-administrator counts. The old `/admin/saas-dispatch/accounts` link redirects here. This directory is implemented in the next release; production promotion is pending explicit approval.

Create an account by selecting an eligible owning operator and entering the legal billing/contact details. Each employer row opens details, editing, **Members** and **Portal access** directly. **Archive account** opens the retained-history removal action with confirmation; it does not permanently delete users, trips, invoices or settlements. Inactive and archived accounts remain searchable. Creation is unavailable when no active operator has corporate management enabled; retained servicing remains available.

The account detail page contains **Members**, mobile role selection, eligible rider search/attachment, **Portal access**, saved trip charges and invoices. Invite a work email under Portal access; the person receives an expiring password setup link. No mobile account or shared password is required for the web portal. Mobile roles are Employee (`member`), Manager (`manager`) and Finance (`finance`). Only eligible active Rider accounts can be attached; changing employers requires removing the existing affiliation first. Archived accounts retain historical trips/invoices and do not accept new membership or bookings.

Platform administrators require `manage_company_accounts_invoicing`, and corporate servicing must be available. If the menu is absent, verify these permissions/capabilities and the deployed release. Do not enable new corporate bookings merely to display retained account records. The tenant entry is `/admin/companies/{company}/corporate-accounts`; it is not an employer-manager login.

## Fleet/operator web staff

Use the existing `/signin_company` login with company/operation-team credentials. Authorized staff open `/company/saas-dispatch/accounts`. Account creation, membership assignment and invoicing require operation-team permissions and the operator's corporate capability. A mobile Manager role does not grant these web permissions.

## Employer web managers and finance

Sign in at **`/corporate/login`**, also linked from the public Sign in account choices. Admin/operator staff first invite the employer's work email from **Portal access** on the correct account. Use the emailed single-use link to set a password of at least 12 characters including letters and numbers; setup/recovery links expire after 60 minutes. The public recovery form does not disclose whether an email is registered. A billing contact is not automatically a login identity.

The corporate browser guard and credentials are separate from `/signin_company`, `/admin/login` and mobile OTP. Each identity belongs to exactly one employer; URL/account IDs cannot select a different employer.

- Corporate administrator: monitor own employer trips; attach/remove existing eligible employees, explicitly assign mobile roles, and manage employer portal invitations/access; read issued invoices/PDFs.
- Finance: monitor own employer trips and read issued invoices/PDFs; cannot manage employees or portal access.
- Activity viewer: monitor own employer trips; cannot read invoices or manage users.

The dashboard shows the employer name/status, active employee count, finalized journey count, outstanding company charges and recent journeys. Quick links expose employee management to corporate administrators and issued invoices to administrators/finance. All data belongs to the signed-in employer. The admin directory marks accounts without an activated portal administrator as **Set up access**; that link opens the invitation controls, not an impersonated employer session.

No corporate browser role can generate, issue, settle or void invoices. Platform/operator finance retains these actions. Inactive identities/accounts are blocked; archived agreements retain read-only historical access. Role/email/status/password changes revoke existing sessions. The last active portal administrator cannot be disabled or demoted. Mail failures retain the pending identity and allow retry; use a functioning configured mail transport before inviting real users.

## Mobile employees and explicitly permissioned managers

Mobile employees use the Customer app's ordinary **phone number + OTP** sign-in. Admin/operator staff or an authorized employer portal administrator attach the Rider to the employer with the default **Employee** role. Profile displays the employer account and own-activity dashboard. Web roles do **not** silently change mobile roles or visibility. An explicitly assigned mobile Manager/Finance role is the account-wide permission exception; only grant it when intended. There is no separate Corporate mode or password login in the mobile app.

- Employee: own corporate trips and metrics.
- Manager: permitted account-wide metrics/trips and member list.
- Finance: permitted account-wide metrics/trips, invoices and authenticated PDFs; no member list.
- Manager invoice access is disabled by default (`CORPORATE_MANAGER_FINANCE_ACCESS=false`).

Roles are server-managed. Knowing an account ID or being a billing contact does not grant access. Employer billing contacts are invoice recipients, not automatically login identities. Missing/revoked membership must not expose account-wide data or silently turn an intended corporate booking into personal billing.

## Acceptance versus production

Fictional Kenyan identities are created only in the guarded local acceptance database. They are not production accounts, and their demo OTP does not establish a production login policy. See [acceptance runbook](../deploy/CORPORATE_ACCEPTANCE.md). Production promotion and booking enablement remain controlled release gates.
