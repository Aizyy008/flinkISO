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
| Postman collection + environment (full API, chained, status-asserted) | `docs/flinkiso-qms-api.postman_collection.json` + `.postman_environment.json` | ✅ |
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
- **63 API routes total.** Import both `docs/flinkiso-qms-api.postman_collection.json` **and**
  `docs/flinkiso-qms-api.postman_environment.json`, select the imported environment (top-right
  environment dropdown), then fill in `login_username` / `login_password` there — never in the
  collection file itself, which is committed to source control. Run **Collection Runner** top to
  bottom (or **Auth → Login** individually first): Login captures `{{token}}` and `{{me_user_id}}`,
  and every Create request captures its own `*_id` variable (`document_id`, `incident_id`,
  `capa_id`, `risk_id`, `kpi_id`, `training_id`, `asset_id`, `haccp_plan_id`, `ccp_id`,
  `validation_id`) consumed automatically by the requests that depend on it — no manual variable
  editing needed between requests. All 36 requests carry a status-code assertion.
- **Test credentials:** `webauth`/JWT both authenticate against the read-only legacy
  `flinkisodb.users` table, so a test login can't be freshly seeded from the Laravel side. Test
  account username: `crf9090@hotmail.com` (active, confirmed working — used for the Newman
  acceptance run in §6). Password is in the shared credentials sheet ("flinkiso v1") — enter it
  into the Postman environment's `login_password` field locally; never write the real password
  into either JSON file (the environment file's committed values are intentionally blank).

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

**Postman/Newman acceptance run, 2026-08-26:** ran the actual collection file (not an approximation)
against staging via `newman run` — **36/36 requests passed, 37/37 assertions passed, 0 failures**,
full top-to-bottom Collection Runner pass with real JWT auth and all resource IDs chained
automatically. Client independently re-ran the same collection and confirmed the same result.

> Note: the KPI forecast returns `insufficient_data` for a single data point (correct missing-data
> rule) and `declining/improving/stable` once ≥2 points exist.

---

## 7. Deploy checklist (staging)

1. FlinkISO: `git pull origin milestone_1.4` → `composer install --no-dev` → `migrate --force` →
   `config:clear && config:cache` → `route:clear` → `view:clear && view:cache`.
2. Set `.env`: `AI_SERVICE_ENABLED/URL/TOKEN` (and re-cache).
3. Stand up `ai-service/` (venv + uvicorn, persistent), set its `.env` (`AI_SERVICE_TOKEN`,
   `OPENAI_API_KEY`).
4. Import the Postman collection + environment, fill in the environment's credentials, run Collection Runner top to bottom.

---

## 8. Known limitations / remaining polish — updated 2026‑08‑26

- ~~AI service deployment is documented but not yet stood up on a server~~ ~~needs a `systemd` unit~~
  **Both DONE, confirmed 2026‑08‑18.** The AI service runs at `/opt/flinkiso-ai`, is registered with
  `systemd` (`flinkiso-ai.service`), enabled on boot, and active on port 8100.
- ~~FlinkISO's own `.env` has no `AI_*` values set~~ **DONE, confirmed 2026-08-19.**
  `AI_SERVICE_ENABLED`, `AI_SERVICE_URL`, and `AI_SERVICE_TOKEN` are set; full chain re-verified
  end-to-end through FlinkISO's own API (real JWT auth, not a direct port-8100 call).
- ~~CAPA suggestions use the rule-based engine — no OpenAI billing credit~~ **DONE, confirmed
  2026-08-20.** Credit was added and independently re-verified (not taken on faith) — `capa-suggest`
  now returns `"engine":"openai:gpt-4o-mini"` with genuinely contextual output.
- ~~Test credentials missing from this handover note~~ **DONE** — see §3 (username named, password
  in the credentials sheet).
- ~~`project_1/DEPLOYMENT.md` is stale~~ **DONE, 2026-08-26** — added a pointer at its top to this
  handover doc's §4/§7 for the M2.2-specific additions (AI service, JWT API, ISO overlays, Postman);
  its own Milestone 1.1 base architecture content is still accurate and left as-is.
- ~~Perfex CRM API integration — open question~~ **CLOSED, confirmed by client in writing 2026-08-19:**
  "mentioned in the estimation notes but not included as a milestone deliverable... if required,
  separate quote." Confirmed out of scope, not a gap.
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
