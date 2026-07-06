# Project 1 — FlinkISO Enterprise QMS Extension

## What the client wants
Extend the existing **FlinkISO** app into a full, ISO-compliant Quality
Management System covering **ISO 9001, 14001, 45001, 22000, 27001, GMP, FDA 21 CFR
Part 11, 13485, 17025**. This is "Package 1 – Core QMS Engine".

---

## ⚠️ CODEBASE AUDIT — read this first (findings from the real `flinkiso/` source)

**The real app is CakePHP 2.10, NOT Laravel.** (The brief said Laravel; the actual
code is CakePHP 2.x on PHP 5.2-era, using ONLYOFFICE for document editing.) This
changes the whole plan — the job is **"extend a legacy CakePHP QMS,"** not build fresh.

### What ALREADY exists (don't rebuild these)
| Feature | Status in current code |
|---|---|
| Document Control | ✅ `qc_documents` + categories + ONLYOFFICE editing |
| **Multi-step approval / workflow engine** | ✅ `approval_processes` + `approval_steps` (routing to HOD/designation/admins/reviewers/approvers; all-vs-any approval; view/edit mode) |
| **Dynamic Form/Table Builder** | ✅ `custom_tables` — JSON field defs, relationships (belongs_to/has_many/child tables), role-based access, versioning, links to approval process |
| **Notification / trigger engine** | ✅ `custom_triggers` — notify users/HODs/admins/departments on field change |
| Audit management | ✅ `tbl_audit_schedule / checklist / findings` |
| Calibration & equipment | ✅ `tbl_calibration`, `tbl_device_equipment` |
| Complaints, suppliers, mgmt review | ✅ `tbl_customer_complaints`, `tbl_supplier_details`, `tbl_mrm` |
| Training, standards/clauses, PDF templates, charts | ✅ present |

**~49 tables already exist.** Much of the "core" is built. A big share of the ISO
overlay fields can likely be delivered by **configuring the existing form builder**,
not writing new modules.

### Serious technical debt (real work + risk lives here)
- 🔴 **All 49 tables are MyISAM** → no transactions, no foreign keys, no crash recovery. **This is unacceptable for FDA 21 CFR Part 11 / audit-trail integrity.** Migrating to InnoDB + immutable audit trails is essential and non-trivial.
- 🔴 **Zero foreign keys** — data integrity is enforced only in app code (fragile).
- 🔴 **God controllers** — `AppController` 5,091 lines, `CustomTablesController` 3,346, `QcDocuments` 1,790. Hard and risky to modify.
- 🔴 Form logic stored as **raw JS text in DB columns** (`add_form_script`/`edit_form_script`) — brittle and a security concern.
- 🔴 **CakePHP 2.10 is end-of-life**; scarce talent, hard to secure/upgrade.

### The real decision (the new spec explicitly asks for this)
For **each module**, state whether you will: **build new / extend / refactor / migrate
to new service.** Recommended split:
- **Extend/configure** what exists: Document Control, Approvals, Audit, Calibration, Form Builder, Triggers → most ISO 9001/14001/45001 overlays.
- **Build new**: HACCP/CCP logs, Risk Matrix, KPI dashboards, GMP/Validation, JWT REST API layer, and the **FastAPI AI microservice** (bolt on as separate services).
- **Migrate/refactor (critical)**: MyISAM→InnoDB + immutable audit trail + e-signature integrity for FDA Part 11.

### Modules to build
- **Document Control v2** — lifecycle (Draft→Review→Approve→Release→Obsolete), versioning, e-signature, change requests, controlled copies, audit trail, PDF export.
- **Audit Management** — annual program, scheduling, checklist builder, findings→NC→CAPA, evidence upload, report PDFs.
- **Training & Competency Matrix** — role-based requirements, skill matrix, sessions/tests, retraining cycles, auto reminders.
- **Asset & Calibration** — asset register, calibration schedule/logs, pass/fail, overdue alerts.
- **KPI Engine** — definitions, targets, dashboards (line/bar/gauge), periodic reports, filters.
- **Workflow Engine** — JSON-based triggers/conditions/actions, reusable across all modules, execution logs.
- **Notification System** — email + in-app, triggered on approvals, deviations, incidents, schedules, etc.
- **Specialized** — Risk Matrix editor, HACCP Plan builder, Incident & CAPA console, GMP/Validation logs.
- **Drag & Drop Form Builder** — full field set (incl. signature, repeatable items), conditional visibility, feeds records + triggers workflows.
- **API Layer** — REST + JWT to integrate Perfex CRM; **FastAPI Python microservice** for AI/ML scoring, predictive KPIs, risk classification, CAPA suggestions, HACCP anomaly detection.

## How & where
- Work directly on the **existing FlinkISO Laravel codebase**.
- Deploy on client's server: **Linux + Plesk panel, MariaDB**. Script already installed and running.
- Code kept in a **GitHub repo**; changes pulled via **SSH**.
- Each deliverable needs working code, demo videos, screenshots, and short technical docs.
- Design **new pages** (do not copy Perfex UI).

## Tech / Language Stack (corrected to the REAL codebase)
| Layer | Reality |
|-------|--------|
| Existing app | **CakePHP 2.10 (MVC, PHP)** — this is what you extend |
| Database | MySQL — **currently MyISAM, must migrate to InnoDB** |
| Doc editing | **ONLYOFFICE** (needs its own separate server) |
| Existing frontend | CakePHP views + Bootstrap + jQuery |
| New AI microservice | **FastAPI (Python 3.11+)** — bolt-on service |
| New API layer | REST + **JWT** (expose QMS events / integrate other systems) |
| PDF | CakePdf plugin (already in project) |

> Note: the original brief named Laravel/Vue — but the delivered code is CakePHP.
> Decide up front: **extend in CakePHP** (faster, but dated) vs. **build new modules
> in Laravel and bridge via API** (cleaner, more effort). Quote accordingly.

## Deliverables
Extended CakePHP modules (controllers/models/views), new modules where needed, REST
endpoints, workflow/form-builder configuration, notifications, PDF templates,
DB migration to InnoDB + immutable audit trail, FastAPI AI service, Postman collection,
full docs, unit + integration test plan.

## Timeline & Budget
- **Timeline:** **4–5 months** (solo, full-time, Claude Code–accelerated)
- **Budget:** **PKR 150,000** total, billed across **4 milestones**

### Milestone breakdown

| # | Milestone | Deliverables | Duration | Payment (PKR) |
|---|-----------|--------------|----------|---------------|
| **M1** | Foundation & Document Control | Data model + all `qms_*` migrations, base architecture, auth/roles, Document Control v2 (lifecycle, versioning, e-signature, change requests, PDF export) | ~4–5 wks | **37,500** |
| **M2** | Audit, CAPA & Core QMS | Audit Management (program, scheduling, checklist builder, findings→NC→CAPA), Incident & CAPA console, Risk Matrix editor | ~4–5 wks | **37,500** |
| **M3** | Training, Assets, KPI & Engines | Training/Competency matrix, Asset & Calibration, KPI engine + dashboards, Workflow engine, Notification system, Drag & Drop Form Builder | ~5 wks | **37,500** |
| **M4** | HACCP, AI Layer & Handover | HACCP plan builder + CCP logs, GMP/Validation logs, REST/JWT API layer, FastAPI AI microservice, testing (unit + integration), docs, Postman collection, handover | ~4–5 wks | **37,500** |

> **Payment terms:** each milestone paid on acceptance (working code + demo video +
> screenshots + brief docs, per client's requirement). Optionally take a 20–25%
> advance on M1 to start.
>
> **Scope note:** PKR 150,000 assumes the scope frozen at quote time. The source doc
> says "do NOT reanalyze / as previously defined" in several places — any new modules
> or compliance features beyond this brief are **re-priced separately**. ISO/FDA
> features (audit trails, e-signatures, validation logs) carry real liability, so keep
> acceptance criteria written per milestone.
