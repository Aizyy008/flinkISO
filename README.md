# FlinkISO Enterprise QMS Extension

Extension of the existing **FlinkISO** application into a full, ISO compliant Quality
Management System covering ISO 9001, 14001, 45001, 22000, 27001, GMP, FDA 21 CFR
Part 11, 13485 and 17025.

## Architecture (hybrid)

The system runs **two applications on one shared MySQL database**, so existing
functionality is preserved while new modules are built on a modern stack.

| Component | Role |
|-----------|------|
| `flinkiso/` | Legacy **CakePHP 2.10** app. Existing QMS operations. Owns its 49 tables. |
| `flinkiso-laravel-api/` | New **Laravel 12** API service. New QMS engines. Owns the `qms_*` tables. |
| FastAPI (planned) | AI microservice for scoring, predictive KPIs and anomaly detection. |

Rules that keep the two apps safe on one database:

- Every table has one owner. CakePHP writes its tables; Laravel writes only `qms_*` tables.
- Laravel reads legacy tables (for example `users` for authentication) but never writes them.
- Database engine is InnoDB across all tables (transactions, integrity, crash recovery).
- Single sign on: Laravel authenticates against the legacy `users` table and issues a JWT.

## Tech stack

- CakePHP 2.10 on PHP 8.2, MySQL / MariaDB, ONLYOFFICE for document editing.
- Laravel 12 API on PHP 8.2, Firebase JWT for the auth bridge.
- Local environment served by LAMPP (`/opt/lampp`).

## Directory structure

```
project_1/
  flinkiso/                 legacy CakePHP application
  flinkiso-laravel-api/     new Laravel API service (qms_* modules)
  backups/                  local database backups (not committed)
```

## Prerequisites

- LAMPP (PHP 8.2 + MariaDB) at `/opt/lampp`, or an equivalent PHP 8.2 + MySQL stack.
- Composer.

## Setup and run

### 1. Database

```bash
sudo /opt/lampp/lampp startmysql

/opt/lampp/bin/mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS flinkiso CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER IF NOT EXISTS 'flinkiso'@'localhost' IDENTIFIED BY 'flinkiso';
GRANT ALL PRIVILEGES ON flinkiso.* TO 'flinkiso'@'localhost';
FLUSH PRIVILEGES;
SQL

# Import legacy schema
/opt/lampp/bin/mysql -u root flinkiso < flinkiso/flinkiso-ver-2x-on-premise/app/webroot/schema/flinkiso-on-premise.sql
```

### 2. Legacy CakePHP app (port 8765)

```bash
cd flinkiso/flinkiso-ver-2x-on-premise/app/webroot
/opt/lampp/bin/php \
  -d session.save_path="$(cd ..; pwd)/tmp/sessions" \
  -S 127.0.0.1:8765 "$(cd ..; pwd)/router.php"
```

Open http://127.0.0.1:8765

### 3. Laravel API service (port 8001)

```bash
cd flinkiso-laravel-api
composer install
/opt/lampp/bin/php artisan migrate --force      # creates the qms_* tables
/opt/lampp/bin/php artisan serve --host=127.0.0.1 --port=8001
```

Health check: http://127.0.0.1:8001/api/health

## Authentication

Both apps use the same account. Get a token from the API with the same credentials
as the CakePHP login:

```bash
curl -s -X POST http://127.0.0.1:8001/api/auth/login \
  -d "username=<user>" -d "password=<password>"
```

Send it as `Authorization: Bearer <token>` on protected routes.

## QMS API modules (Milestone 1)

All under `/api/qms`, JWT protected:

- Document Control (lifecycle, versioning, controlled copies, change requests)
- Incidents and Non conformities
- CAPA (corrective and preventive actions, effectiveness verification)
- Risk register with automatic scoring
- Evidence store
- Workflow engine (triggers, conditions, actions)
- Notifications
- Immutable audit trail with an integrity check endpoint (FDA 21 CFR Part 11)

## Status

- Phase 0 (foundation, database migration, auth bridge): complete.
- Milestone 1 (Core QMS Engine): complete.
- Milestone 2 (KPI, HACCP, GMP, REST API, AI microservice): planned.
