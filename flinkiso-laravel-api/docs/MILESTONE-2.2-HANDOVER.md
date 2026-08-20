# Milestone 2.2 (M4) — Delivery & Handover

**Scope (from `FlinkISO_Milestone distribution.docx`, Week 4):** complete HACCP/GMP,
REST API + JWT, Postman collection, ISO overlay fields (14001/45001/27001/13485/17025),
FastAPI AI microservice, testing, documentation, handover.

Branch: **`milestone_1.4`**, merged into **`main`** on 2026‑08‑07 (contains all of M1–M3 + M2.2 + post-landing
hardening). Status: **implemented, tested, and deployed to staging** (`main` is the branch now deployed to
`qms.flinkiso.dctrd.us` — see the 2026‑08‑16 update in §8 and the new §10 below).

---

## 1. What was delivered

| Deliverable | Where | Status |
|---|---|---|
| REST API + JWT for the M2.1 modules (KPI, Training, Calibration, HACCP) | `app/Http/Controllers/Api/Qms/*` + `routes/api.php` | ✅ |
| GMP / Validation logging (module + UI + API) | `ValidationController` (web+API), `qms_validations`, `resources/views/validations/*` | ✅ |
| ISO overlay fields (5 standards), config-driven | `config/iso_overlays.php` + `iso_standard`/`iso_overlay` on `qms_incidents` | ✅ |
| FastAPI AI microservice (risk / KPI forecast / CAPA suggest / HACCP anomaly) | `ai-service/` (Python) | ✅ |
| FlinkISO ↔ AI integration | `config/ai.php`, `app/Services/Ai/AiClient.php`, `Api/Qms/AiController.php` | ✅ |
| Postman collection (full API) | `docs/flinkiso-qms-api.postman_collection.json` | ✅ |
| Testing | 23-check e2e (see §6) | ✅ |

The FlinkISO REST API for the **M1 modules** (documents, incidents, CAPA, risk, evidence,
workflows, notifications, audit-trail) already existed; M4 extended it to the M2.1 modules and
added GMP/Validation + AI.

---

## 2. Migrations added (all additive)

```
2026_07_20_180000_create_qms_validation_tables      # qms_validations
2026_07_20_190000_add_iso_overlay_to_qms_incidents  # iso_standard + iso_overlay (json)
```
Deploy: `php artisan migrate --force`.

---

## 3. REST API + JWT

- **Auth:** `POST /api/auth/login` (username + FlinkISO password) → `{ token }`. Send it as
  `Authorization: Bearer <token>` on every `/api/*` call. `JWT_SECRET` + `JWT_TTL_MINUTES` in `.env`.
- **Base:** `/api` (public: `/health`, `/auth/login`; protected: everything under `jwt` middleware).
- **Modules under `/api/qms/`:** documents, incidents (+ISO overlay), capa, risks, evidence,
  workflows, notifications, audit-trail, **kpis** (+ `/dashboard`, `/results`, `/forecast`),
  **trainings**, **assets** (+calibrations), **haccp** (plans/steps/hazards/ccps/logs),
  **validations**, and **ai** (health, risk-score, capa-suggest, anomaly).
- **63 API routes total.** Import `docs/flinkiso-qms-api.postman_collection.json`, set `{{base_url}}`,
  run **Auth → Login** (fill the password) to capture `{{token}}`, then run any request.
- **Test credentials — ⚠️ MISSING, needs filling in before this counts as a complete handover note**
  (the client's acceptance doc explicitly requires "test credentials" in the final handover note; this
  doc doesn't have any yet). `webauth`/JWT both authenticate against the read-only legacy
  `flinkisodb.users` table, so a test login can't be freshly seeded from the Laravel side — either name
  an existing QMS user here, or have one created directly in `flinkisodb.users` and record its
  username here (never the password itself — reference where it's stored, e.g.
  `DEPLOY-CREDENTIALS.txt`).

---

## 4. AI microservice (`ai-service/`)

Standalone FastAPI service — **all outputs are computed from inputs (not mock)**:

| Endpoint | Does | Method |
|---|---|---|
| `POST /ai/risk-score` | FMEA RPN = L×S×D → band + priority | deterministic |
| `POST /ai/kpi-forecast` | least-squares trend on KPI history → next-period forecast, trend, R² confidence, breach risk | deterministic |
| `POST /ai/haccp-anomaly` | limit breaches + >3σ outliers + drift | deterministic |
| `POST /ai/capa-suggest` | root-cause + corrective/preventive actions | **OpenAI** when `OPENAI_API_KEY` set, else input-driven rule engine |

**Run it:**
```bash
cd project_1/ai-service
./run.sh                      # creates venv, installs deps, starts on :8100
# or manually:
python3 -m venv .venv && . .venv/bin/activate && pip install -r requirements.txt
cp .env.example .env          # set AI_SERVICE_TOKEN, and OPENAI_API_KEY (client_files/OpenAI API Key.txt)
uvicorn app.main:app --host 0.0.0.0 --port 8100
```
**Connect FlinkISO** — add to FlinkISO `.env`, then `config:cache`:
```
AI_SERVICE_ENABLED=true
AI_SERVICE_URL=http://127.0.0.1:8100        # or the deployed AI host
AI_SERVICE_TOKEN=<same token as the AI service>
```
FlinkISO endpoints that call it with real record data:
`POST /api/qms/ai/risk-score`, `/api/qms/kpis/{id}/forecast`,
`/api/qms/incidents/{id}/capa-suggest`, `/api/qms/haccp/ccps/{ccpId}/anomaly`.

**Deploy on Plesk:** host the service on its own subdomain/port (e.g. `ai.dctrd.us`) with a Python app +
a persistent `uvicorn` process (Plesk Python app or a `supervisord`/systemd worker). Keep
`AI_SERVICE_TOKEN` and `OPENAI_API_KEY` in its `.env` only.

---

## 5. ISO overlay fields

`config/iso_overlays.php` defines standard-specific fields for **ISO 14001, 45001, 27001, 13485,
17025**. On an Incident, pick the ISO standard and its fields appear (web) / are accepted (API,
`iso_standard` + `iso_overlay`). Unknown overlay keys are filtered (mass-assignment safe). Add a
standard or field by editing the config — **no migration**.

---

## 6. Testing (23 checks, all green)

JWT (401 without token, `/me` with token) · KPI dashboard calculated · KPI/Training/Asset create +
calibration status · **HACCP CCP 68<72 → deviation → auto Incident + linked CAPA**, 74 → ok ·
Validation create + approve · **ISO overlay stored for all 5 standards** + unknown-key filtering ·
AI health, risk-score (critical), KPI forecast from real results (declining), CAPA suggest from a
real incident, HACCP anomaly from CCP logs. AI service unit outputs verified independently.

> Note: the KPI forecast returns `insufficient_data` for a single data point (correct missing-data
> rule) and `declining/improving/stable` once ≥2 points exist.

---

## 7. Deploy checklist (staging)

1. FlinkISO: `git pull origin milestone_1.4` → `composer install --no-dev` → `migrate --force` →
   `config:clear && config:cache` → `route:clear` → `view:clear && view:cache`.
2. Set `.env`: `AI_SERVICE_ENABLED/URL/TOKEN` (and re-cache).
3. Stand up `ai-service/` (venv + uvicorn, persistent), set its `.env` (`AI_SERVICE_TOKEN`,
   `OPENAI_API_KEY`).
4. Import the Postman collection, log in, smoke-test the endpoints.

---

## 8. Known limitations / remaining polish — updated 2026‑08‑19

- ~~AI service deployment is documented but not yet stood up on a server~~ ~~needs a `systemd` unit~~
  **Both DONE, confirmed 2026‑08‑18.** The AI service runs at `/opt/flinkiso-ai`, is registered with
  `systemd` (`flinkiso-ai.service`), enabled on boot, and active on port 8100.
- **Still open: FlinkISO's own `.env` has no `AI_*` values set at all** (`grep '^AI_' .env` on the
  server returns nothing, checked 2026‑08‑19). FlinkISO cannot call the AI service until
  `AI_SERVICE_ENABLED=true`, `AI_SERVICE_URL=http://127.0.0.1:8100`, and `AI_SERVICE_TOKEN` are added.
  The AI host **does** enforce a bearer token (confirmed: `POST /ai/risk-score` without a header returns
  `401 Invalid or missing bearer token`) — get the exact value from whoever has root
  (`sudo grep AI_SERVICE_TOKEN /opt/flinkiso-ai/.env`), it's not readable by the app's own deploy user.
- **CAPA suggestions** use the rule-based engine until a working LLM key is set on the AI host. The
  OpenAI key on file is valid but the OpenAI account has **no billing credit**
  (`insufficient_quota`/`credit_balance_exhausted`) — add credit at platform.openai.com → Billing, or
  switch `AI_PROVIDER` to Anthropic/Gemini/Ollama (the provider abstraction in `ai-service/app/providers.py`
  already supports all of these — just needs that provider's key/env vars set on the AI host).
  *(Client confirmed 2026‑08‑18 credit will be added; not yet verified as of 2026‑08‑19.)*
- **Test credentials missing from this handover note** — see §3, flagged there. The client's acceptance
  doc explicitly requires this; needs a real QMS-side username filled in before final sign-off.
- **`project_1/DEPLOYMENT.md` (the standalone setup/migration guide) is stale** — it still documents the
  `milestone_1.2` deploy flow and has no mention of the AI service, JWT API, or ISO overlays added in
  M2.2. The client's acceptance criterion ("we can follow the setup/migration guide") is satisfied by
  §7 of *this* handover doc, but if the client specifically opens `DEPLOYMENT.md` they'll find outdated
  content — worth syncing before calling documentation fully complete.
- **Open question, not confirmed either way:** the earliest client specs (`Project 1.pdf`,
  `FlickISO Upgrate.pdf`) describe the REST API layer as built specifically "to integrate Perfex CRM"
  (pulling HR/assets/products/manufacturing data from a Perfex CRM instance). No Perfex CRM integration
  exists anywhere in this codebase, and the later, authoritative `FlinkISO_Milestone distribution.docx`
  (the doc that actually gates payment) describes the API generically with **no mention of Perfex CRM at
  all** — consistent with this having been superseded during scope negotiation, the same way the ZaiKPI
  integration scope was later reduced by `Exceptions.txt`. Treated here as dropped, but **this has never
  been explicitly confirmed with the client in writing** — worth a one-line confirmation before final
  sign-off so it can't resurface as a "missing deliverable" later.
- ISO overlays are implemented on **Incidents** (the core NC/deviation record), 5 fields per standard.
  `FlinkISO QMS Expansion.pdf` (the estimation reference doc) lists a much larger menu of possible fields
  per standard (~20+ for ISO 14001 alone) — the 5-field set implemented is an intentional curated subset
  that satisfies the binding acceptance test ("we can see ISO-specific overlay fields in the relevant
  records/screens"), not a literal implementation of that doc's full estimation menu. Extend via
  `config/iso_overlays.php` (no migration needed) if the client wants more fields or overlays on records
  beyond Incidents. *(Open — optional, not requested yet.)*
- Demo videos/screenshots (a docx deliverable) are not produced here — capture during the staging demo.
  *(Still open.)*
- Email from `qms@dctrd.us`: DKIM + DMARC TXT records **added and verified resolving in OVH DNS,
  confirmed 2026‑08‑18.** SPF was already live. *(Not M2.2 scope, but bundled into the same go-live push
  — now fully done.)*

---

## 10. Post-landing hardening (2026‑07‑21 → 2026‑08‑07) — not in the original §1 delivery table

`milestone_1.4` kept moving after the initial M2.2 commit; this handover doc previously only described
the day‑one state. The following was added afterward and is included in what's now deployed on `main`:

- E‑signature requirement extended to **GMP/Validation** and **HACCP plan** approvals, each with automated
  tests (previously e‑signatures only covered Document Control Approve/Release).
- Two production-breaking Blade bugs fixed: KPI detail page 500 (`@endif@if` adjacency broke compilation)
  and Form Builder page 500 (`@json` couldn't parse an inline multi-line closure).
- Form Builder submissions can now feed **CAPA, Audit, HACCP, and GMP/Validation** records (previously
  only Incident + Risk).
- ZaiKPI sync (`ZaiKpiClient`) now transfers the ISO **clause** alongside the standard, not just the standard.
- A round of M1.2/M2/M3 acceptance fixes: role-based Document Control workflow (Creator→Reviewer→
  Approver→Publisher, each lifecycle action visible only to the role that can perform it), required-field
  validation made non-silent, independent review per document version, single-role test accounts for
  clean acceptance testing, CAPA "Closed" disabled until effectiveness is verified, admin authorization
  fixes, real task records, evidence filename handling, de-duplicated same-day reminders.
- A real automated test suite now exists (`tests/Feature/Qms/{MilestoneTwoTest,MilestoneTwoFixesTest,
  MilestoneThreeTest,MilestoneFourTest,FormBuilderFeedTest,FormBuilderValidationFeedTest,ZaiKpiSyncTest}.php`)
  — the root `README.md`'s "no committed test suite" line is stale.

**Current blockers to calling M2.2 fully live are access-gated, not code-gated** (see §8): OpenAI billing
credit, root access to register the `systemd` unit, and OVH DNS access for the email records.

---

## 11. New/changed files (register)

**New (FlinkISO):** `Api/Qms/{KpiController,TrainingController,CalibrationController,HaccpController,ValidationController,AiController}.php`,
`Web/ValidationController.php`, `Models/Qms/Validation.php`, `Services/Ai/AiClient.php`,
`config/{iso_overlays,ai}.php`, `resources/views/validations/*`, the 2 migrations,
`docs/flinkiso-qms-api.postman_collection.json`.
**Modified:** `routes/{api,web}.php`, `app/Providers/AppServiceProvider.php` (AiClient binding),
`Models/Qms/Incident.php` (iso_overlay cast), `Web/IncidentController.php` + `Api/Qms/IncidentController.php`
(overlay), `resources/views/incidents/{create,show}.blade.php`, `resources/views/layout.blade.php` (sidebar).
**New (AI):** `ai-service/` (Python FastAPI: `app/{main,engine}.py`, `requirements.txt`, `.env.example`, `run.sh`).
