# FlinkISO QMS Test Guide

Complete test guide, organized by the client's weekly milestones (1.1, 1.2, 2.1, 2.2).
Each section lists what is implemented, where to test it, step by step tests, and the
acceptance points to confirm.

All new work is additive in the Laravel service. The legacy CakePHP app and its 49
tables are untouched and re verified after every change.

---

## Prerequisites (run this once per session)

Start the three pieces:

```bash
# 1) MySQL
sudo /opt/lampp/lampp startmysql

# 2) Legacy CakePHP app (port 8765)
cd "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/webroot"
/opt/lampp/bin/php -d session.save_path="$(cd ..; pwd)/tmp/sessions" -S 127.0.0.1:8765 "$(cd ..; pwd)/router.php" &

# 3) Laravel QMS service (port 8001)
cd "/home/dev/Documents/noor projects/project_1/flinkiso-laravel-api"
/opt/lampp/bin/php artisan serve --host=127.0.0.1 --port=8001 &
```

Login account for both apps: `admin@flinkiso.local` / `admin@flinkiso.local`

Two ways to test:
- UI: the QMS web UI at http://127.0.0.1:8001
- API: get a token, then call endpoints:
  ```bash
  B=http://127.0.0.1:8001/api
  TOKEN=$(curl -s -X POST $B/auth/login -d "username=admin@flinkiso.local" -d "password=admin@flinkiso.local" | php -r 'echo json_decode(file_get_contents("php://stdin"),true)["access_token"];')
  AUTH="Authorization: Bearer $TOKEN"
  ```

---

## Milestone 1.1  Document Control, Audit Trail, Electronic Signatures

Status: complete. The UI uses FlinkISO's own AdminLTE skin blue theme (same header,
sidebar, boxes and Font Awesome as the existing app) so it matches the project design.

Implemented: lifecycle state machine (draft, review, approve, release, obsolete),
edit document details (logged as an edit event), versioning and version history,
change requests that increment the version, controlled copies, PDF export with status,
version and approval metadata, an immutable hash chained audit trail, and electronic
signatures on approve and release. A sample document (SOP 001) can be seeded with
`php artisan db:seed --class=QmsDemoSeeder`.

Audit trail cannot be edited or deleted from the UI (append only, tamper evident), and
released or obsolete documents cannot be edited (a change request and new version is
required instead).

### Where to test
Browser UI: http://127.0.0.1:8001 (log in, then Document Control in the sidebar).

### Test steps (UI)
1. Log in at http://127.0.0.1:8001
2. Click New document. Enter a number (for example SOP 010), title, category. Create. It starts as draft.
3. While in draft, click Edit details and change the title or category, Save. This is recorded as an edit event in the audit trail.
4. On the document page click Submit for review, then Approve (e sign), then Release (e sign). Confirm you cannot skip a step.
4. Click Export PDF. Confirm the PDF shows document number, status, current version, and the approval/signature record.
5. Under Change request, enter a reason and raise it. Then click Implement (new version) on that request. Confirm the version increments and the document returns to draft.
6. Take the document to released again, then use Issue controlled copy (holder and location).
7. Scroll to Audit trail. Confirm each action appears with the user and the signature meaning.

### Acceptance points to confirm
- Create a document and move it through review, approval, release, obsolete. Yes.
- Create a change request and see the version increment. Yes.
- View previous versions and identify the current released version. Yes.
- Export a controlled document to PDF with correct metadata. Yes.
- See audit trail entries for each major action. Yes.
- See electronic signature information linked to the approve/release action. Yes.

### Integrity test (optional, technical)
```bash
curl -s $B/qms/audit-trail/verify -H "$AUTH"     # -> {"valid":true,...}
```
The audit trail is append only and hash chained; editing any row breaks the chain.

---

## Milestone 1.2  Incident, CAPA, Risk, Workflow, Notifications

Status: engine and API complete and tested. UI screens are the next task.

### Where to test
API for now (endpoints below). UI screens to follow.

### Test steps (API)
```bash
# Optional rule: a critical incident auto raises a CAPA and a notification
curl -s -X POST $B/qms/workflows -H "$AUTH" -H "Content-Type: application/json" -d '{
  "name":"Critical incident auto CAPA","trigger_event":"incident.created",
  "conditions":[{"field":"severity","op":"=","value":"critical"}],
  "actions":[{"type":"create_capa","params":{"title":"CAPA for critical incident"}},
             {"type":"notify","params":{"title":"Critical incident logged","email":true}}]}'

# 1) Create a critical incident (triggers the workflow)
curl -s -X POST $B/qms/incidents -H "$AUTH" -H "Content-Type: application/json" \
  -d '{"title":"Cold storage temp deviation","type":"deviation","severity":"critical","source":"ccp"}'

# 2) See the CAPA auto created from the incident, then verify effectiveness
curl -s $B/qms/capa -H "$AUTH"
# POST /qms/capa/{id}/verify with effectiveness_notes + verified=true

# 3) See the notification the workflow created
curl -s $B/qms/notifications -H "$AUTH"

# 4) Create a risk and see the score/level calculated (4 x 5 x 3 = 60, critical)
curl -s -X POST $B/qms/risks -H "$AUTH" -H "Content-Type: application/json" \
  -d '{"title":"Supplier contamination","standard":"22000","likelihood":4,"severity":5,"detection":3}'

# 5) Add evidence to a record
# POST /qms/evidence with related_type, related_id, evidence_type, json_data

# 6) Audit trail integrity
curl -s $B/qms/audit-trail/verify -H "$AUTH"
```

### Acceptance points to confirm
- Create an incident/deviation, classify, add evidence, assign, move through statuses. Engine yes; UI pending.
- Create a CAPA from an incident and track to closure. Yes (workflow auto creates it).
- Run an effectiveness check before closing a CAPA. Yes.
- Create risks and see risk score calculation. Yes.
- Trigger a workflow rule and see the action/state change. Yes.
- Receive or view notifications. In app yes; email needs client SMTP.

### Remaining for 1.2
- UI screens for Incident/Deviation, CAPA, Risk, and Notifications.
- Wire real email (needs client SMTP settings).

---

## Milestone 2.1  KPI, Training, Calibration, Forms, HACCP Start

Status: not started. Will be filled in when built.

Planned tests: define KPIs and see dashboard results; assign training by role and see
competency/expiry reminders; view calibration schedules with due/overdue alerts; submit
a custom form through the bridge; create the initial HACCP structure and log CCP data.

---

## Milestone 2.2  HACCP/GMP, API, AI, Testing, Documentation, Handover

Status: not started. Will be filled in when built.

Planned tests: complete a HACCP flow where a CCP deviation creates or links to a CAPA;
view GMP/Validation logs; authenticate with JWT and run the Postman collection; see ISO
overlay fields; call the FastAPI AI service and receive real outputs; follow the
setup/migration guide.

---

## API endpoint reference (all under /api/qms, JWT protected)

- Documents: GET/POST `/documents`, GET `/documents/{id}`, PATCH `/documents/{id}/transition`, POST `/documents/{id}/version`, POST `/documents/{id}/change-request`, POST `/documents/{id}/copy`
- Incidents: GET/POST `/incidents`, GET `/incidents/{id}`, PATCH `/incidents/{id}/status`
- CAPA: GET/POST `/capa`, GET `/capa/{id}`, PATCH `/capa/{id}/status`, POST `/capa/{id}/verify`
- Risks: GET/POST `/risks`, GET `/risks/{id}`, PATCH `/risks/{id}`
- Evidence: GET/POST `/evidence`
- Workflows: GET/POST `/workflows`, GET `/workflows/{id}/runs`
- Notifications: GET `/notifications`, PATCH `/notifications/{id}/read`
- Audit trail: GET `/audit-trail`, GET `/audit-trail/verify`

Document Control also has a web UI at http://127.0.0.1:8001/documents.
