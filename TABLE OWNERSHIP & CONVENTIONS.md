# FlinkISO Hybrid — Table Ownership & Coding Conventions

The hybrid runs **two apps on one MySQL database** (`flinkiso`):
- **CakePHP 2.10** (legacy) — existing QMS operations
- **Laravel 12 API** (`flinkiso-laravel-api`) — new Phase-1 engines + AI/API layer

To avoid data conflicts, every table has **exactly one owner** (the app allowed to
write it). The other app may only read.

---

## 🔑 The golden rule
> **Never let two apps write the same row.**
> CakePHP writes its tables. Laravel writes only new `qms_*` tables.
> Laravel may READ legacy tables; it must NOT write them.

---

## Ownership map

### 🟩 Owned by CakePHP (Laravel = READ-ONLY)
All 49 existing tables, e.g.:
`users`, `companies`, `branches`, `departments`, `designations`, `employees`,
`standards`, `clauses`, `processes`, `qc_documents`, `qc_document_categories`,
`approvals`, `approval_processes`, `approval_steps`, `custom_tables`,
`custom_triggers`, `tbl_audit_*`, `tbl_calibration_*`, `tbl_device_equipment_*`,
`tbl_customer_complaints_*`, `tbl_supplier_details_*`, `tbl_mrm_*`, `histories`,
`files`, `pdf_templates`, `graph_panels`, `schedules`, `settings`-related, etc.

Laravel reads these for auth and cross-references (e.g., `users`, `standards`,
`employees`, `departments`). **Laravel writes to none of them.**

### 🟦 Owned by Laravel (new — CakePHP does NOT touch)
All new modules use the **`qms_` prefix**. Planned (built in later phases):
`qms_risks`, `qms_controls`, `qms_incidents`, `qms_capa`, `qms_evidence`,
`qms_kpis`, `qms_kpi_values`, `qms_haccp_plans`, `qms_haccp_ccp_logs`,
`qms_validations`, `qms_workflows`, `qms_workflow_runs`, `qms_notifications`,
`qms_audit_trail` (immutable, FDA Part 11).

### 🟨 Shared reference (read by both, written by owner only)
Legacy master data (`companies`, `branches`, `departments`, `employees`,
`standards`, `clauses`) is written by CakePHP and **read** by Laravel `qms_*`
modules via `*_id` columns. Laravel stores the legacy UUID as a plain reference.

---

## Conventions

### Database
- **Engine:** InnoDB, `ROW_FORMAT=DYNAMIC` (migration done in Phase 0.2).
- **Primary keys:** legacy tables use `varchar(36)` UUIDs — new `qms_*` tables follow the same UUID style for consistency and easy cross-reference.
- **Foreign keys:** allowed *within* the `qms_*` set. FKs from `qms_*` → legacy tables are avoided (legacy schema still evolving); store the UUID and validate in code.
- **No Laravel infra tables in this DB:** cache/session/queue use `file`/`sync` drivers so `migrations`, `jobs`, `cache`, `sessions` etc. never appear in `flinkiso`.

### Auth bridge
- Laravel authenticates against the legacy `users` table.
- Password check: `md5(Security.salt . password)` (`FlinkUser::verifyPassword`).
- On success Laravel issues a **JWT** (HS256, `JWT_SECRET`); protected routes use the `jwt` middleware.
- The CakePHP `Security.salt` and `JWT_SECRET` live in the Laravel `.env`.

### Code
- Laravel legacy models set `$timestamps = false`, `$incrementing = false`, `$keyType = 'string'`, and are treated as read-only.
- New modules: standard Laravel (Eloquent models, migrations, form-request validation, resource controllers) under the `qms_*` namespace.
- API routes live in `routes/api.php`, prefixed `/api`.

---

## Ports (local dev)
| Service | URL |
|---------|-----|
| CakePHP app | http://127.0.0.1:8765 |
| Laravel API | http://127.0.0.1:8001 |
| MySQL | 127.0.0.1:3306 (socket `/opt/lampp/var/mysql/mysql.sock`) |
