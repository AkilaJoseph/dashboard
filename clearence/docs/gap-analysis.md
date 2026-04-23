# Gap Analysis — ACIMS Technical Specification vs. Codebase

**Project:** MUST Automated Clearance Information Management System  
**Specification Version:** 1.0  
**Analysis Date:** 2026-04-23  
**Analyst:** Claude (static analysis, no code execution)

---

## Legend

| Symbol | Meaning |
|---|---|
| ✅ | Fully implemented — spec requirement met |
| 🟡 | Partially implemented — present but incomplete or divergent in a minor way |
| 🔴 | Missing — spec requires it; codebase has nothing |
| ⚠️ | Divergent — implemented differently in a way that conflicts with the spec |
| ➕ | Extra — in codebase, not in spec (no gap, but worth noting) |

---

## Executive Summary

| Category | Count |
|---|---|
| ✅ Fully implemented | 28 |
| 🟡 Partially implemented | 12 |
| 🔴 Missing | 21 |
| ⚠️ Divergent | 6 |
| ➕ Extra (beyond spec) | 10 |

**Overall maturity:** The core clearance workflow, certificate generation, QR verification, and departmental review are fully functional and exceed the spec in several areas. The primary gaps are: (1) the `awaiting_final` workflow step and admin final-approval gate; (2) file-attachment support; (3) password security policy; (4) the `audit_logs` table; (5) testing suite; and (6) a handful of missing admin features (CSV import, CSV/PDF export, account lockout, certificate revocation as a separate admin action).

---

## Section 4 — Functional Requirements

### 4.1.1 Registration and Authentication

| Req | Status | Notes |
|---|---|---|
| Register with registration number, name, faculty, programme, email | ✅ | `register.blade.php` collects all fields; stored in `users` table |
| Unique registration number and email validation | ✅ | `RegisterController` validates uniqueness |
| Password minimum 8 chars, mixed case, numeric, special char | 🔴 | Current policy is `min:6` only (`RegisterController`); no complexity rules |
| Login via email/registration number + password | 🟡 | Email login works; registration-number login not supported |
| Forgot Password (time-bounded reset email) | 🔴 | No password reset routes or views exist |
| Account lockout after 5 failed attempts | 🔴 | No `ThrottleLogins` trait or `RateLimiter` on login |

### 4.1.2 Profile Management

| Req | Status | Notes |
|---|---|---|
| View and update phone, postal address, profile picture | 🟡 | `profile/show.blade.php` allows name/email/password; no postal address or profile picture |
| Restrict academic identifiers to admin-only edit | 🟡 | No explicit guard; profile form doesn't expose them but no backend enforcement |

### 4.1.3 Clearance Request Submission

| Req | Status | Notes |
|---|---|---|
| Initiate request by selecting clearance type | ✅ | `student/clearances/create.blade.php` + `ClearanceController@store` |
| Auto-attach relevant departments for selected type | ✅ | `ClearanceController@store` seeds approval rows per department |
| Attach supporting documents (PDF/JPG/PNG ≤5 MB, up to 5 files) | 🔴 | No `attachments` table, no file upload in request form, no `AttachmentController` |
| Generate unique reference number per request | 🟡 | `reference_no` computed dynamically as `CL-{year}-{id}` in the model; not stored as a DB column |
| Prevent duplicate in-progress submissions | ✅ | `ClearanceController@store` checks for active request before allowing new submission |

### 4.1.4 Clearance Tracking

| Req | Status | Notes |
|---|---|---|
| Dashboard showing real-time clearance status | ✅ | `student/dashboard.blade.php` shows active request + per-dept status |
| Per-department status (Pending / Approved / Rejected) | ✅ | `student/clearances/show.blade.php` shows per-dept rows |
| Display officer comments | ✅ | Comments shown on show view |
| Notify student via in-app alerts on status change | ✅ | `DatabaseNotification` dispatched on approval/rejection |
| Email notification on status change | 🟡 | In-app notifications done; email dispatch is present in some events but not all status changes |

### 4.1.5 Certificate Download

| Req | Status | Notes |
|---|---|---|
| Download PDF once all departments approved + admin final approval | ⚠️ | Certificate available once all depts approve; there is no separate admin final-approval step (see §7.3) |
| Embed unique QR code on certificate | ✅ | `CertificateController@generate` uses `chillerlan/php-qrcode` |
| Block download until Fully Approved | ✅ | Controller guards on status |

### 4.2.1 Department Request Inbox

| Req | Status | Notes |
|---|---|---|
| Dashboard listing pending requests for department | ✅ | `officer/approvals/index.blade.php` |
| Filter by status, date, clearance type, programme | 🟡 | Status and date filters present; clearance-type and programme filters missing |
| Search by registration number or name | ✅ | Search by name/student ID present |

### 4.2.2 Review and Decision

| Req | Status | Notes |
|---|---|---|
| Display student info and attachments | 🟡 | Student info shown; attachments section absent (no attachment feature) |
| Approve or reject request | ✅ | `OfficerApprovalController@update` |
| Mandatory comment on rejection | ✅ | Backend validation enforces comment when status = rejected |
| Optional comment on approval | ✅ | Comment field present but not required |
| Record decision, timestamp, officer identity (immutable) | ✅ | `reviewed_at` + `reviewed_by` stored; no UPDATE path for decided rows |

### 4.2.3 Departmental Reporting

| Req | Status | Notes |
|---|---|---|
| Summary statistics (total, approved, rejected, avg turnaround) | ✅ | `officer/dashboard.blade.php` shows counts; turnaround stats present |
| Export request list as CSV or PDF | 🔴 | No export route or download action in officer controllers |

### 4.3.1 User Management

| Req | Status | Notes |
|---|---|---|
| Create, view, edit, deactivate, delete user accounts | ✅ | `admin/users/*` CRUD views and `AdminUserController` |
| Reset user passwords and unlock accounts | 🟡 | Admin password reset present; no "unlock" action (account lockout not implemented) |
| Bulk student import from CSV | 🔴 | `admin/sims/sync.blade.php` pulls from SIMS scraper; no CSV upload import |

### 4.3.2 Department Management

| Req | Status | Notes |
|---|---|---|
| Create, edit, deactivate departments | ✅ | `admin/departments/*` CRUD |
| Assign/unassign officers to department | ✅ | Officer assignment in `admin/users/edit.blade.php` via `department_id` |
| Configure routing order per clearance type | 🔴 | Departments are applied in a fixed order; no routing-order configuration UI |

### 4.3.3 Final Approval and Certification

| Req | Status | Notes |
|---|---|---|
| Admin grants final clearance only after all dept approvals | ⚠️ | System auto-approves when all depts approve; no admin final-approval step |
| Digitally sign certificate with institutional stamp and admin name | 🟡 | Certificate includes MUST branding; no admin name/signature field populated from the approving admin |
| Revoke previously issued certificate with reason + timestamp | 🔴 | Certificate ledger records issuances; no revocation action |

### 4.3.4 System Reporting

| Req | Status | Notes |
|---|---|---|
| Institutional reports (cleared per faculty, avg duration, rejection trends) | 🟡 | `admin/dashboard.blade.php` shows aggregate counts; no dedicated reports page with faculty/programme breakdown |
| Filter reports by academic year, semester, programme, department | 🔴 | No report filters |
| Export reports in PDF and CSV | 🔴 | No export actions in admin controllers |

---

## Section 5 — Non-Functional Requirements

### 5.1 Security

| Req | Status | Notes |
|---|---|---|
| HTTPS / TLS 1.2+ in transit | 🟡 | Development uses HTTP; no enforced HTTPS redirect in app config |
| bcrypt password hashing | ✅ | Laravel default (`Hash::make`) |
| CSRF protection on all state-changing requests | ✅ | `@csrf` in every form; `VerifyCsrfToken` middleware active |
| Input sanitisation (SQL injection, XSS) | ✅ | Eloquent ORM + Blade escaping |
| Session timeout after 30 minutes inactivity | 🔴 | `session.lifetime` not set to 30; Laravel default is 120 minutes |

### 5.2 Performance

| Req | Status | Notes |
|---|---|---|
| Page load < 3 s under 200 concurrent users | 🟡 | No load testing conducted; CDN Tailwind is a blocking render resource (Lighthouse) |
| DB indexing on FK and frequently queried fields | 🟡 | Standard Laravel FK indexes present; prediction composite index added; not all columns indexed |
| Server-side caching (Redis / file) for static reference data | 🔴 | No cache calls for departments or clearance types in controllers |

### 5.3 Usability

| Req | Status | Notes |
|---|---|---|
| Responsive design (320 px – 1920 px) | ✅ | Tailwind responsive utilities throughout |
| Contextual feedback for every action | ✅ | Flash success/error banners in layout |
| Consistent navigation and visual hierarchy | ✅ | Shared sidebar layout; consistent styling |
| Inline help section and downloadable user manual | 🔴 | No help pages or user manual |

### 5.4 Scalability

| Req | Status | Notes |
|---|---|---|
| Stateless server tier | ✅ | Session in DB/file; no local state that blocks horizontal scaling |
| Schema normalised to 3NF | 🟡 | Core schema is mostly normalised; student fields flattened into `users` violates 3NF slightly |
| Docker deployment support | 🔴 | No `Dockerfile` or `docker-compose.yml` |

### 5.5 Reliability and Availability

| Req | Status | Notes |
|---|---|---|
| 99% availability during academic calendar | 🟡 | Not tested; XAMPP dev environment only |
| Daily DB backups, 30-day retention | 🔴 | No backup script or schedule |
| Log critical errors and unhandled exceptions | 🟡 | Laravel default `storage/logs/laravel.log`; no admin log viewer |

### 5.6 Maintainability

| Req | Status | Notes |
|---|---|---|
| PSR-12 coding standard | 🟡 | Generally follows conventions; not verified with PHP-CS-Fixer |
| PHPDoc comments on all non-trivial classes | 🔴 | No PHPDoc blocks in any controller or service |
| Git with clear branching strategy | 🟡 | Git used; single `main` branch — no documented branching strategy |

---

## Section 6 — Database Design

| Spec Table | Status | Notes |
|---|---|---|
| `users` | ✅ | Present; adds extra columns (`student_id`, `registration_number`, `programme`, `college`, `year_of_study`, `phone`, `locale`) |
| `students` (separate 1:1 table) | ⚠️ | **Not created.** Student academic fields are flattened directly into `users`. Different normalisation decision. |
| `departments` | ✅ | Present; has `code`, `name`, `description`, `is_active` |
| `clearance_requests` (named `clearances`) | 🟡 | Present; `student_id` FK points to `users.id` not `students.id`; `reference_no` not stored as column |
| `approvals` (named `clearance_approvals`) | ✅ | Present; `reviewed_at` = `decided_at`, `reviewed_by` = `officer_id` |
| `attachments` | 🔴 | Table does not exist |
| `notifications` | ⚠️ | Uses Laravel's polymorphic `notifications` table; spec defines a custom table with `type ENUM(info\|success\|warning\|error)` |
| `audit_logs` | 🔴 | Table does not exist; no audit trail mechanism |

### Extra tables in codebase (beyond spec)

| Table | Purpose |
|---|---|
| `certificate_ledger` | Tamper-evident cryptographic log of certificate issuances |
| `push_subscriptions` | VAPID/WebPush subscriber keys |
| `push_campaigns` | Admin broadcast campaigns |
| `sims_syncs` | SIMS scraper sync records |
| `qr_tokens` | QR scan tokens for student ID verification |

---

## Section 7 — System Workflow

| Step | Status | Notes |
|---|---|---|
| 7.1 Request initiation + confirmation email | 🟡 | Request creation works; confirmation email not sent on submission |
| 7.2 Departmental review with in-app + email notification | 🟡 | In-app notification dispatched; email on approval not confirmed for all paths |
| 7.3 `awaiting_final` status + admin final-approval step | ⚠️ | **Not implemented.** System transitions directly from all-approved to `approved`; no admin review gate |
| 7.4 Rejection handling; student may resubmit | ✅ | Rejection propagates to overall status; student can submit new request after rejection |
| 7.5 PDF certificate with QR code on final approval | ✅ | `CertificateController@generate` produces PDF with QR |

---

## Section 8 — Roles and Permissions

| Req | Status | Notes |
|---|---|---|
| Student permissions | ✅ | Enforced via `RoleMiddleware('student')` |
| Department staff permissions | ✅ | Enforced via `RoleMiddleware('officer')` |
| Administrator permissions | ✅ | Enforced via `RoleMiddleware('admin')` |
| Laravel Gates and Policies | 🔴 | Only `RoleMiddleware` used; no `Gate::define` or `Policy` classes in `app/Policies/` |

---

## Section 9 — API and Interfaces

| Req | Status | Notes |
|---|---|---|
| RESTful JSON API under `/api` prefix | ✅ | `routes/api.php` with JSON responses |
| Sanctum token-based auth for API | ⚠️ | API routes protected by `auth:sanctum` middleware declaration but the actual `/api/auth/login` token-issue endpoint is not implemented; web session bleeds into API |
| `POST /api/auth/login` — issue token | 🔴 | Not implemented |
| `POST /api/auth/logout` — revoke token | 🔴 | Not implemented |
| `GET /api/student/profile` | 🔴 | Not implemented |
| `POST /api/clearance/requests` | 🔴 | Not implemented as API endpoint; only web form |
| `GET /api/clearance/requests/{id}` | 🔴 | Not implemented |
| `GET /api/department/inbox` | 🔴 | Not implemented |
| `POST /api/department/approvals/{id}` | 🔴 | Not implemented |
| `GET /api/admin/reports` | 🔴 | Not implemented |
| `GET /verify/{reference_no}` | ✅ | `certificate/verify.blade.php` + `CertificateController@verify` |
| SMTP email service | 🟡 | Mailtrap configured in `.env.example`; not all notification events send email |
| DomPDF for PDF generation | ✅ | `barryvdh/laravel-dompdf` installed and used |
| chillerlan/php-qrcode | ✅ | Installed; used in certificate and QR token features |

---

## Section 10 — Input / Output Design

| Req | Status | Notes |
|---|---|---|
| Clearance type, academic year/semester, reason, attachments, declaration checkbox | 🟡 | Type and reason present; academic year/semester fields absent; attachments and declaration checkbox absent |
| Registration form fields per spec | ✅ | All spec fields present in `register.blade.php` |
| Approval/rejection form with mandatory rejection comment | ✅ | Enforced in backend validation |
| Student dashboard with status cards and progress | ✅ | `student/dashboard.blade.php` |
| Dept dashboard with pending list and metrics | ✅ | `officer/dashboard.blade.php` |
| Admin dashboard with institutional stats and bottleneck info | ✅ | `admin/dashboard.blade.php` with prediction/bottleneck widget |
| Clearance Status Report | 🟡 | Admin sees clearance list; no formal exportable report |
| Departmental Performance Report | 🟡 | Counts present; no export, no turnaround breakdown per officer |
| Audit Trail Report | 🔴 | No audit log table or view |
| Certificate: logo, letterhead, approval chain, admin signature, QR | 🟡 | Logo, chain, QR present; admin signature/name not dynamically populated |

---

## Section 11 — Security Considerations

| Req | Status | Notes |
|---|---|---|
| Laravel Breeze/Fortify session auth for web | ✅ | Custom auth implemented following same pattern |
| Sanctum tokens for API | 🔴 | Sanctum installed but token-issue flow not implemented |
| bcrypt hashing | ✅ | |
| Laravel Gates, Policies, middleware | 🔴 | Gates/Policies absent; middleware-only RBAC |
| Optional 2FA for admin accounts | 🔴 | Not implemented |
| Laravel Form Requests for all validation | 🔴 | Inline validation in controllers; no `app/Http/Requests/` classes |
| File upload MIME/extension/size validation | 🔴 | No file upload feature exists |
| Files stored outside public web root | 🔴 | No file upload feature exists |
| CSRF protection | ✅ | |
| XSS via Blade escaping | ✅ | |
| SQL injection via Eloquent | ✅ | |
| Rate limiting on login | 🟡 | `throttle:6,1` on API; web login has no explicit throttle |
| Brute-force lockout after 5 failures | 🔴 | Not implemented |
| `$fillable` / `$guarded` on models | ✅ | All Eloquent models declare `$fillable` |

---

## Section 12 — Testing Strategy

| Req | Status | Notes |
|---|---|---|
| PHPUnit unit tests (70 % coverage) | 🔴 | No `tests/Unit/` or `tests/Feature/` files beyond default stubs |
| Feature/integration tests (submission, approval, rejection, cert) | 🔴 | Not written |
| System testing on staging | 🔴 | No staging environment |
| User Acceptance Testing with UAT report | 🔴 | Not done |
| Security testing (OWASP ZAP) | 🔴 | Not done |

---

## Extra Features in Codebase (beyond spec) ➕

These are additions not in the spec but implemented:

| Feature | Location |
|---|---|
| PWA / Service Worker + offline page | `public/sw.js`, `offline.blade.php` |
| Push notifications (WebPush / VAPID) | `PushService`, `push_subscriptions` table |
| Push campaign management | `admin/push-campaigns/*` |
| SIMS integration (external student record scraper) | `admin/sims/*`, `SimsSyncController` |
| QR token scanning for student ID | `officer/scan.blade.php`, `qr_tokens` table |
| Certificate ledger (tamper-evident log) | `certificate_ledger` table, `CertificateLedger` model |
| Predictive completion estimates | `PredictionService`, `prediction-widget.blade.php` |
| Swahili / English locale switcher | `LocaleController`, `lang/sw/` |
| Notification preferences (per-channel settings) | `profile/show.blade.php` notification tab |
| Real-time bottleneck alerts with reminder push | `BottleneckReminderController`, `bottleneck-widget.blade.php` |

---

## Quick Wins (Low effort, high spec-compliance value)

| # | Item | Effort | Impact |
|---|---|---|---|
| QW1 | Set `session.lifetime = 30` in `config/session.php` | 1 line | §5.1 ✅ |
| QW2 | Strengthen password rule to `min:8\|regex:…` in `RegisterController` | 5 lines | §4.1.1 ✅ |
| QW3 | Add `reference_no` as a stored DB column (migration + model) | 1 migration | §6.1.4 ✅ |
| QW4 | Add `throttle:5,1` middleware to web login route | 1 line in `routes/web.php` | §11.3 🟡 |
| QW5 | Add `academic_year` and `semester` fields to the clearance request form | 1 migration + form edit | §10.1.1 🟡 |
| QW6 | Add PHPDoc blocks to `PredictionService` and the three new controllers | ~30 lines | §5.6 🟡 |
| QW7 | Store `final_approver_id` in `clearances` and display admin name on certificate | 1 migration + view edit | §10.2.3 🟡 |

---

## Major Gaps (Require significant effort)

| # | Gap | Spec Reference | Complexity |
|---|---|---|---|
| G1 | **`awaiting_final` workflow step + admin final-approval UI** | §7.3, §4.3.3 | Medium |
| G2 | **File attachments** (`attachments` table, upload form, storage outside public root, MIME validation) | §4.1.3, §6.1.6, §11.2 | High |
| G3 | **Forgot Password** (reset email, token store, reset form) | §4.1.1 | Medium (Laravel built-in) |
| G4 | **Laravel Gates and Policies** replacing raw role-string checks | §8, §11.1 | Medium |
| G5 | **`audit_logs` table** + observer hooks on critical models | §6.1.8, §10.2.2 | Medium |
| G6 | **REST API token endpoints** (login, logout, profile, requests) using Sanctum | §9 | High |
| G7 | **CSV/PDF export** for departmental and institutional reports | §4.2.3, §4.3.4 | Medium |
| G8 | **PHPUnit test suite** (unit + feature, 70 % coverage) | §12 | High |
| G9 | **Certificate revocation** admin action | §4.3.3 | Low-Medium |
| G10 | **Account lockout** after 5 failed login attempts | §4.1.1 | Medium |

---

## Recommended Implementation Order

Priority is based on spec weight and dependency chain.

```
Phase 1 — Security & Auth baseline
  QW2  Password strength policy
  QW4  Login rate limiting
  G3   Forgot Password (use Laravel built-in `password_resets`)
  G10  Account lockout (RateLimiter + `status=locked` flag)

Phase 2 — Workflow correctness
  G1   awaiting_final status + admin final-approval step
  QW7  final_approver_id on certificate
  QW3  reference_no as stored column

Phase 3 — Data completeness
  G2   File attachments (blocking for spec compliance on request form)
  QW5  academic_year + semester fields on request

Phase 4 — Access control hardening
  G4   Laravel Policies (ClearancePolicy, ApprovalPolicy, CertificatePolicy)

Phase 5 — Auditability
  G5   audit_logs table + Eloquent observers

Phase 6 — Reporting & Export
  G7   CSV/PDF export (departmental + institutional)
  QW1  Session timeout = 30 min

Phase 7 — API surface
  G6   Sanctum token-issue/revoke + API endpoints for core workflow

Phase 8 — Quality
  G8   PHPUnit tests (unit: services, policies; feature: full clearance lifecycle)
  QW6  PHPDoc comments
  G9   Certificate revocation
```

---

## Architectural Divergence Notes

### D1 — `students` table not separate
**Spec:** §6.1.2 defines a separate `students` table (1:1 with `users`).  
**Codebase:** Student fields (`student_id`, `registration_number`, `programme`, `college`, `year_of_study`, `phone`) are columns on `users`.  
**Impact:** FK in `clearances` points to `users.id` rather than `students.id`. Violates the spec's stated 3NF normalisation. Not a blocker for functionality, but note for viva.

### D2 — `notifications` table schema
**Spec:** §6.1.7 defines a custom `notifications` table with `type ENUM(info|success|warning|error)`.  
**Codebase:** Uses Laravel's polymorphic `notifications` table (UUID PK, `notifiable_type`, `notifiable_id`, `data` JSON). Functionally equivalent; schema differs.

### D3 — `clearances.status` missing `awaiting_final`
**Spec:** Status ENUM includes `pending | in_progress | approved | rejected | revoked`.  
**Codebase:** Has `pending | in_progress | approved | rejected`; missing `awaiting_final` (workflow step) and `revoked` (revocation action).

### D4 — API authentication
**Spec:** §9 — Sanctum token auth for all API routes.  
**Codebase:** `auth:sanctum` middleware applied but no token-issue endpoint; web session cookie used for API calls in practice.

---

*End of gap analysis. No code was modified during this analysis.*
