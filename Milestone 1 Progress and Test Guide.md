# Milestone 1 Core QMS Engine — Progress and Browser Test Guide

Everything below was built additively in the Laravel API service. The legacy CakePHP
app is untouched and was re verified after every step.

## Status: Milestone 1 complete

| Module | Status |
|--------|--------|
| Document Control v2 (lifecycle, versioning, controlled copies, change requests) | Done |
| Immutable hash chained audit trail (FDA 21 CFR Part 11) | Done |
| Electronic signature integrity | Done |
| Incident / Non conformity / Deviation | Done |
| CAPA (corrective/preventive, effectiveness) | Done |
| Shared Evidence store | Done |
| Risk Register + Risk Matrix (auto scoring) | Done |
| Workflow / state machine engine | Done |
| Notifications (in app + email hook) | Done |

12 new `qms_` tables (all InnoDB). CakePHP's 49 tables verified intact.

## What I already tested (all passed)
- Document Control full lifecycle: draft to review to approved to released to obsolete; illegal jumps blocked (422); versioning to v2; change requests; controlled copies only on released.
- Workflow engine: a critical incident auto created a CAPA and a notification.
- Risk scoring: likelihood 4 x severity 5 x detection 3 = 60 (critical).
- CAPA effectiveness electronic signature.
- Audit trail: every write logged; chain verify valid.
- Tamper test: editing one audit row was detected (valid=NO, broken_at=seq); restore made it valid again.
- CakePHP regression: login, dashboard, documents all still load. Nothing broke.

## Delivery note

Milestone 1 is delivered as the QMS engine and its REST API in the Laravel service.
These modules are ready to be surfaced in the FlinkISO user interface (the UI placement
is a pending decision with the client). Testing is done through the API.

## How to run and verify

### Step 1: make sure both servers are running
```bash
sudo /opt/lampp/lampp startmysql            # if MySQL is not running

# Laravel API
cd "/home/dev/Documents/noor projects/project_1/flinkiso-laravel-api"
/opt/lampp/bin/php artisan serve --host=127.0.0.1 --port=8001
```

### Step 2: get a token (shared with the CakePHP login)
```bash
B=http://127.0.0.1:8001/api
TOKEN=$(curl -s -X POST $B/auth/login -d "username=admin@flinkiso.local" -d "password=admin@flinkiso.local" | php -r 'echo json_decode(file_get_contents("php://stdin"),true)["access_token"];')
AUTH="Authorization: Bearer $TOKEN"
```

### Step 3: exercise the modules
```bash
# Optional: create the rule that auto raises a CAPA + notification on a critical incident
curl -s -X POST $B/qms/workflows -H "$AUTH" -H "Content-Type: application/json" -d '{
  "name":"Critical incident auto CAPA","trigger_event":"incident.created",
  "conditions":[{"field":"severity","op":"=","value":"critical"}],
  "actions":[{"type":"create_capa","params":{"title":"CAPA for critical incident"}},
             {"type":"notify","params":{"title":"Critical incident logged","email":true}}]}'

# Document Control: create, then move through the lifecycle
DOC=$(curl -s -X POST $B/qms/documents -H "$AUTH" -H "Content-Type: application/json" -d '{"doc_number":"SOP 001","title":"Cleaning SOP","category":"SOP"}')

# Incident (critical triggers the workflow), CAPA, Risk
curl -s -X POST $B/qms/incidents -H "$AUTH" -H "Content-Type: application/json" -d '{"title":"Temp deviation","type":"deviation","severity":"critical"}'
curl -s $B/qms/capa -H "$AUTH"
curl -s -X POST $B/qms/risks -H "$AUTH" -H "Content-Type: application/json" -d '{"title":"Supplier risk","standard":"22000","likelihood":4,"severity":5,"detection":3}'

# Audit trail integrity check
curl -s $B/qms/audit-trail/verify -H "$AUTH"       # -> {"valid":true,...}
```

## API endpoints (all under /api/qms, JWT protected)
- Documents: GET/POST `/documents`, GET `/documents/{id}`, PATCH `/documents/{id}/transition`, POST `/documents/{id}/version`, POST `/documents/{id}/change-request`, POST `/documents/{id}/copy`
- Incidents: GET/POST `/incidents`, GET `/incidents/{id}`, PATCH `/incidents/{id}/status`
- CAPA: GET/POST `/capa`, GET `/capa/{id}`, PATCH `/capa/{id}/status`, POST `/capa/{id}/verify`
- Risks: GET/POST `/risks`, GET `/risks/{id}`, PATCH `/risks/{id}`
- Evidence: GET/POST `/evidence`
- Workflows: GET/POST `/workflows`, GET `/workflows/{id}/runs`
- Notifications: GET `/notifications`, PATCH `/notifications/{id}/read`
- Audit trail: GET `/audit-trail`, GET `/audit-trail/verify`
