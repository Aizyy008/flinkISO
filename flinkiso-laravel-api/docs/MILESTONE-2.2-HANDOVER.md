# Milestone 2.2 (M4) — Delivery & Handover

**Scope (from `FlinkISO_Milestone distribution.docx`, Week 4):** complete HACCP/GMP,
REST API + JWT, Postman collection, ISO overlay fields (14001/45001/27001/13485/17025),
FastAPI AI microservice, testing, documentation, handover.

Branch: **`milestone_1.4`** (contains all of M1–M3). Status: **implemented + tested locally**.

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

## 8. Known limitations / remaining polish

- **AI service deployment** is documented but not yet stood up on a server (needs a Python host +
  persistent process). The service + integration are complete and tested locally.
- **CAPA suggestions** use the rule-based engine until `OPENAI_API_KEY` is set on the AI host; then
  they use OpenAI automatically.
- ISO overlays are implemented on **Incidents** (the core NC/deviation record). Extend the same
  config-driven pattern to other records if the client wants overlays elsewhere.
- Demo videos/screenshots (a docx deliverable) are not produced here — capture during the staging demo.

---

## 9. New/changed files (register)

**New (FlinkISO):** `Api/Qms/{KpiController,TrainingController,CalibrationController,HaccpController,ValidationController,AiController}.php`,
`Web/ValidationController.php`, `Models/Qms/Validation.php`, `Services/Ai/AiClient.php`,
`config/{iso_overlays,ai}.php`, `resources/views/validations/*`, the 2 migrations,
`docs/flinkiso-qms-api.postman_collection.json`.
**Modified:** `routes/{api,web}.php`, `app/Providers/AppServiceProvider.php` (AiClient binding),
`Models/Qms/Incident.php` (iso_overlay cast), `Web/IncidentController.php` + `Api/Qms/IncidentController.php`
(overlay), `resources/views/incidents/{create,show}.blade.php`, `resources/views/layout.blade.php` (sidebar).
**New (AI):** `ai-service/` (Python FastAPI: `app/{main,engine}.py`, `requirements.txt`, `.env.example`, `run.sh`).
