# Changelog

All notable changes in this workspace for the feature branch.

## 2026-07-30 — Stabilize tests, migrations, invoice numbering, scheduled reports

- Fix: Guarded `activity_log` migrations to avoid duplicate-create and duplicate-index errors when running migrations in CI/test environments.
- Fix: Added `ip_address`, `user_agent`, `batch_uuid`, and `event` handling to activity log schema where needed.
- Fix: `InvoiceNumberGenerator` now initializes sequence from existing invoices and provides `generateInvoiceNumber()` to fill gaps in number sequences.
- Fix: `InvoiceService` now exposes `generateInvoiceNumber()` to match tests and UI expectations.
- Fix: Update `UpdateManualInvoiceRequest` validation to mirror store rules and require `invoice_number`.
- Feature: Scheduled reports implementation (models, jobs, controllers, views, Vue components, and tests).
- Tests: Multiple unit and feature tests added/updated; full test suite now passes locally.

## Notes

- Migrations were adjusted to be safe when run against different DB drivers; ensure CI uses the MySQL test DB configured in `phpunit.xml`.

PR description (suggested): Stabilize test suite, fix activity log migrations, add robust invoice number generation, and implement scheduled reports with full test coverage.
