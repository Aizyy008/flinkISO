# FlinkISO QMS — Module-by-Module Implementation Plan

**Architecture:** Hybrid — existing **CakePHP 2.10** (legacy ops) + new **Laravel API**
(Phase-1 core engines) + **FastAPI** (AI microservice). All share **one MySQL DB**
(migrated MyISAM → InnoDB).

**Legend**
- 🟩 **Extend** = configure/reuse what already exists in CakePHP
- 🟦 **Build (Laravel)** = new module in the Laravel API service
- 🟪 **Build (FastAPI)** = Python AI microservice
- 🟥 **Refactor/Migrate** = rework existing (DB/engine/integrity)

**Ownership rule:** CakePHP keeps writing its existing tables. Laravel *owns* all new
`qms_*` tables and mostly *reads* legacy ones. Never let both apps write the same row.

---

## PHASE 0 — Foundation & Setup *(do this first, everything depends on it)*

| # | Task | Home | Action | Effort | Notes |
|---|------|------|--------|--------|-------|
| 0.1 | Get server access, run existing CakePHP app locally + on server | CakePHP | Setup | 1–2 d | Linux + Composer + DB (client provides) |
| 0.2 | Full DB backup, then **migrate MyISAM → InnoDB** | MySQL | 🟥 Migrate | 2–3 d | Add PKs/indexes; verify app still runs |
| 0.3 | Stand up **Laravel API service** (skeleton, shared DB connection, read legacy tables) | Laravel | 🟦 Build | 2 d | Laravel 10, Sanctum/Passport for JWT |
| 0.4 | **Auth bridge / SSO** — Laravel issues JWT, trusts CakePHP session/user | Both | 🟦 Build | 2–3 d | Single login across both apps |
| 0.5 | **Table-ownership map** + shared coding conventions doc | Docs | Build | 1 d | Prevents write conflicts |

**Subtotal: ~8–11 days**

---

## MILESTONE 1 — Core Foundation & Document Control

| # | Module | Home | Action | What to do | Effort |
|---|--------|------|--------|-----------|--------|
| 1.1 | Org/master data (company, branch, dept, site, process, roles) | CakePHP | 🟩 Extend | Add missing `site`, `work center`, `process→clause` mapping fields | 2–3 d |
| 1.2 | Standards & clause mapping (9001…17025) | CakePHP | 🟩 Extend | Seed all standards/clauses; site & dept applicability | 2 d |
| 1.3 | **Document Control v2** (lifecycle, versioning, controlled copies) | CakePHP | 🟩 Extend | Extend `qc_documents`; formalize Draft→Review→Approve→Release→Obsolete | 4–5 d |
| 1.4 | **E-signature + immutable audit trail** (FDA Part 11) | Laravel | 🟥 Refactor | Harden `histories` into tamper-evident log (hash chain, signer, reason, timestamp) | 5–6 d |
| 1.5 | Change Request workflow (CR form + approval) | CakePHP | 🟩 Extend | Use existing `document_change_requests` + approval engine | 2 d |
| 1.6 | PDF export w/ version/footer metadata | CakePHP | 🟩 Extend | Reuse CakePdf plugin | 2 d |

**Subtotal: ~17–20 days** → **Deliverable M1**

---

## MILESTONE 2 — Audit, CAPA & Risk Core

| # | Module | Home | Action | What to do | Effort |
|---|--------|------|--------|-----------|--------|
| 2.1 | **Audit Management** (program, schedule, checklist, findings) | CakePHP | 🟩 Extend | Extend `tbl_audit_*`; link findings → NC → CAPA | 3–4 d |
| 2.2 | **CAPA module** (corrective/preventive, effectiveness, closure) | Laravel | 🟦 Build | **New** — 0 exists today. Full CAPA lifecycle + evidence | 5–6 d |
| 2.3 | **Incident / Non-conformity / Deviation** | Laravel | 🟦 Build | **New** — capture, classify, root cause, link to CAPA | 4–5 d |
| 2.4 | **Risk Register + Risk Matrix** | Laravel | 🟦 Build | **New** — likelihood×severity×detection scoring, matrix editor | 4–5 d |
| 2.5 | Evidence & records store (files, photos, measurements) | Laravel | 🟦 Build | Shared `qms_evidence` used by all modules | 2–3 d |

**Subtotal: ~18–23 days** → **Deliverable M2**

---

## MILESTONE 3 — Engines, Training, Assets & KPI

| # | Module | Home | Action | What to do | Effort |
|---|--------|------|--------|-----------|--------|
| 3.1 | **Workflow / State-machine engine** | Laravel | 🟦 Build | Triggers→conditions→actions (assign task, create CAPA, notify, request approval); reusable | 6–7 d |
| 3.2 | **Notification system** (email + in-app) | Laravel | 🟦 Build | Queue-based; reuse concepts from CakePHP `custom_triggers` | 3–4 d |
| 3.3 | **Dynamic Form Builder** | CakePHP + Laravel | 🟩 Extend + 🟦 Bridge | Keep CakePHP `custom_tables` UI; expose submissions to Laravel engine via API | 4–5 d |
| 3.4 | **Training & Competency Matrix** | CakePHP | 🟩 Extend | Skill matrix per role/site, retraining cycles, expiry reminders | 3–4 d |
| 3.5 | **Asset & Calibration** | CakePHP | 🟩 Extend | Extend `tbl_calibration` / `tbl_device_equipment`; due/overdue alerts, link to CCP | 3–4 d |
| 3.6 | **KPI Engine + dashboards** | Laravel | 🟦 Build | **New** — KPI defs, targets, aggregation, thresholds; charts (reuse `graph_panels` data) | 5–6 d |

**Subtotal: ~24–30 days** → **Deliverable M3**

---

## MILESTONE 4 — HACCP, AI Layer, API & Handover

| # | Module | Home | Action | What to do | Effort |
|---|--------|------|--------|-----------|--------|
| 4.1 | **HACCP Plan Builder** (products, flow, hazards, PRP/OPRP/CCP) | Laravel | 🟦 Build | **New** — food-safety plans, critical limits | 4–5 d |
| 4.2 | **CCP Monitoring logs + auto-deviation → CAPA** | Laravel | 🟦 Build | **New** — real-time critical-limit checks trigger incident/CAPA | 4–5 d |
| 4.3 | **GMP / Validation logging** (IQ/OQ/PQ, batch records) | Laravel | 🟦 Build | **New** — validation records + data-integrity flags | 3–4 d |
| 4.4 | **REST API Layer (JWT)** — expose QMS events / integrate CRM | Laravel | 🟦 Build | Standard REST + JWT; Postman collection | 3–4 d |
| 4.5 | **FastAPI AI microservice** | FastAPI | 🟪 Build | Risk scoring, predictive KPIs, CAPA suggestions, HACCP anomaly detection | 6–8 d |
| 4.6 | ISO overlay fields (14001 / 45001 / 27001 / 13485 / 17025 specifics) | CakePHP form builder | 🟩 Extend | Configure per-standard fields via existing builder | 5–6 d |
| 4.7 | Testing (unit + integration), docs, Postman, handover | All | Build | Full test plan + demo videos + docs | 5–6 d |

**Subtotal: ~30–38 days** → **Deliverable M4 (final)**

---

## Effort Summary

| Phase | Range (days) |
|-------|--------------|
| Phase 0 — Foundation | 8–11 |
| Milestone 1 | 17–20 |
| Milestone 2 | 18–23 |
| Milestone 3 | 24–30 |
| Milestone 4 | 30–38 |
| **Total** | **~97–122 working days (≈ 4–5 months)** |

Matches the agreed **PKR 150,000 across 4 milestones** (PKR 37,500 each).

---

## Build Order (dependencies)
1. **Phase 0** (setup, DB migration, Laravel skeleton, auth) — nothing works without this.
2. **Evidence store + Workflow engine + Notifications** are shared foundations — build early (they're in M2/M3 but many modules depend on them; pull forward if possible).
3. **CAPA** before Audit/Incident/HACCP finalization (they all feed into CAPA).
4. **API + FastAPI** last (they consume finished modules).

## What's genuinely NEW vs EXTEND (quick tally)
- 🟩 **Extend existing CakePHP:** Document Control, Audit, Calibration, Training, Form Builder UI, ISO overlay fields, org/standards data.
- 🟦 **Build new in Laravel:** CAPA, Incident/NC, Risk Register, Workflow engine, Notifications, KPI engine, HACCP + CCP, GMP/Validation, REST API, e-sign/audit-trail hardening.
- 🟪 **Build new in FastAPI:** AI microservice.
