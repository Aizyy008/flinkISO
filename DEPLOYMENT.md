# FlinkISO QMS — Deployment Plan

Repeatable plan for deploying the Laravel QMS service. Written for Milestone 1.1 but
the same process applies to every later milestone (weeks 2, 3, 4).

Credentials are in `../DEPLOY-CREDENTIALS.txt` (kept outside the repo).

## Architecture (two apps, two databases, one server)

| App | URL | Database | Notes |
|-----|-----|----------|-------|
| CakePHP (existing FlinkISO) | https://flinkiso.dctrd.us | `flinkisodb` | Client's live app. We do NOT modify it. |
| Laravel QMS (new) | https://qms.flinkiso.dctrd.us | `qmsdb` | Owns all `qms_*` tables. |

The Laravel app uses **two DB connections**:
- default (`qmsdb`) for its own `qms_*` tables (read/write).
- `flinkiso` (`flinkisodb`) read only, for login (`users`) and reference data (`standards`).

Legacy records are referenced by plain UUID (no cross database foreign keys), so the two
databases can even be on different servers.

## Repository layout (monorepo)

Both apps live in one repo. Only the Laravel folder is deployed to the qms subdomain:

```
<repo root>/
  flinkiso/              legacy CakePHP (reference only; the live copy is the client's flinkiso.dctrd.us)
  flinkiso-laravel-api/  <- deploy THIS ; subdomain document root = flinkiso-laravel-api/public
```

So on the server: clone the repo, then set the **qms.flinkiso.dctrd.us document root to
`<clone path>/flinkiso-laravel-api/public`**. Do not deploy the `flinkiso/` folder.

## Prerequisites (confirm with client)

- SSH or Plesk access.
- Subdomain `qms.flinkiso.dctrd.us` with document root set to `flinkiso-laravel-api/public`.
- PHP 8.2+ (8.3 recommended) and Composer on the server.
- Both databases reachable (same MySQL server ideally).
- Production CakePHP `Security.salt` value.

## Plesk walkthrough (Milestone 1.1) — exact steps

Server: Plesk Obsidian, subscription `dctrd.us`. Use the domain PHP 8.3 CLI at
`/opt/plesk/php/8.3/bin/php` for all artisan/composer commands.

### Step 1 — PHP 8.3 (done)
Panel → Websites & Domains → qms.flinkiso.dctrd.us → PHP → version 8.3.32 → Apply.

### Step 2 — Open SSH
Panel → Websites & Domains → qms.flinkiso.dctrd.us → **SSH Terminal**.
Find the subdomain folder (usually `/var/www/vhosts/dctrd.us/qms.flinkiso.dctrd.us`):
```bash
cd /var/www/vhosts/dctrd.us/qms.flinkiso.dctrd.us
pwd && ls        # confirm you are in the qms subdomain folder (it has httpdocs)
```

### Step 3 — Read the production Security.salt (needed for login)
```bash
grep -R "Security.salt" /var/www/vhosts/dctrd.us/flinkiso.dctrd.us/*/app/Config/core.php
# copy the 40-char value; you will paste it into .env below
```
(If the path differs, use Panel → File Manager on flinkiso.dctrd.us to open `app/Config/core.php`.)

### Step 4 — Clone the repo (monorepo; public, no auth)
```bash
git clone https://github.com/Aizyy008/flinkISO.git app
cd app/flinkiso-laravel-api
```

### Step 5 — Install dependencies (with PHP 8.3)
```bash
curl -sS https://getcomposer.org/installer | /opt/plesk/php/8.3/bin/php
/opt/plesk/php/8.3/bin/php composer.phar install --no-dev --optimize-autoloader
```
(Or use Panel → Dev Tools → **PHP Composer** pointed at `app/flinkiso-laravel-api`.)

### Step 6 — Configure .env
```bash
cp .env.example .env
/opt/plesk/php/8.3/bin/php artisan key:generate
nano .env
```
Set these values (rest can stay default):
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qms.flinkiso.dctrd.us

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qmsdb
DB_USERNAME=qmsdb
DB_PASSWORD=8m&XtvkD3te?rq9V
# remove the DB_SOCKET line

FLINKISO_DB_HOST=127.0.0.1
FLINKISO_DB_PORT=3306
FLINKISO_DB_DATABASE=flinkisodb
FLINKISO_DB_USERNAME=flinkisodb
FLINKISO_DB_PASSWORD=?*QfebQVppbhh746
# remove the FLINKISO_DB_SOCKET line

FLINKISO_SECURITY_SALT=<<paste the salt from Step 3>>
JWT_SECRET=<<paste a long random string>>
JWT_TTL_MINUTES=120

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```
Save (Ctrl+O, Enter, Ctrl+X).

### Step 7 — Create tables + optimise
```bash
/opt/plesk/php/8.3/bin/php artisan migrate --force
/opt/plesk/php/8.3/bin/php artisan db:seed --class=QmsDemoSeeder --force
chmod -R ug+rw storage bootstrap/cache
/opt/plesk/php/8.3/bin/php artisan config:cache
/opt/plesk/php/8.3/bin/php artisan route:cache
/opt/plesk/php/8.3/bin/php artisan view:cache
```

### Step 8 — Point the subdomain at Laravel's public folder
Panel → Websites & Domains → qms.flinkiso.dctrd.us → **Hosting & DNS → Hosting Settings**
→ **Document root** → change from `httpdocs` to:
```
app/flinkiso-laravel-api/public
```
→ Apply/OK.

### Step 9 — SSL
Panel → qms.flinkiso.dctrd.us → **SSL/TLS Certificates** → install a free **Let's Encrypt**
certificate → enable "redirect HTTP to HTTPS".

### Step 10 — Test
- https://qms.flinkiso.dctrd.us/api/health → both `qms_db` and `legacy_db` connected.
- https://qms.flinkiso.dctrd.us/login → sign in with a FlinkISO account (e.g. crf9090@hotmail.com)
  → Document Control loads → run create → review → approve → release → PDF → audit.

---

## Milestone 1.2 deployment (redeploy) — simple Plesk steps

Milestone 1.2 adds Incidents, CAPA, Risk, Workflow rules, Notifications and email.
It only adds code + new `qms_*` migrations, so redeploy is short.

### Step 1 — Pull the milestone 1.2 code (Plesk SSH)
```bash
cd /var/www/vhosts/dctrd.us/qms.flinkiso.dctrd.us/app
git fetch origin
git checkout milestone_1.2
git pull origin milestone_1.2
cd flinkiso-laravel-api
/opt/plesk/php/8.3/bin/php composer.phar install --no-dev --optimize-autoloader
/opt/plesk/php/8.3/bin/php artisan migrate --force
```

### Step 2 — Add SMTP + notification settings to .env
Append these to `.env` (edit with `nano .env`). Use real SMTP values; leave the
override EMPTY in production so each email goes to the real assignee.
```
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=oraluxph@gmail.com
MAIL_PASSWORD=pupeqaemunveyrlg
MAIL_FROM_ADDRESS="oraluxph@gmail.com"
MAIL_FROM_NAME="FlinkISO QMS"

# Staging only: send all emails to one inbox. LEAVE EMPTY on production.
QMS_NOTIFY_OVERRIDE_EMAIL=
```

### Step 3 — Rebuild caches
```bash
/opt/plesk/php/8.3/bin/php artisan config:cache
/opt/plesk/php/8.3/bin/php artisan route:cache
/opt/plesk/php/8.3/bin/php artisan view:cache
```

### Step 4 — Schedule the daily overdue reminders
The overdue email reminders run via the Laravel scheduler. Add ONE cron entry.
Panel → **qms.flinkiso.dctrd.us → Scheduled Tasks → Add Task**:
- Run: **Command**
- Command:
  ```
  /opt/plesk/php/8.3/bin/php /var/www/vhosts/dctrd.us/qms.flinkiso.dctrd.us/app/flinkiso-laravel-api/artisan schedule:run
  ```
- Schedule: **every minute** (`* * * * *`)

(The scheduler itself only fires the reminder once a day at 08:00; the per-minute cron
just lets Laravel decide when to run.)

### Step 5 — Test milestone 1.2
- Log in → sidebar shows Incidents, CAPA, Risk Register, Workflow rules, Notifications.
- Create a workflow rule (incident.created, severity = critical → create CAPA + notify).
- Create a critical incident → a CAPA is auto created and linked, incident becomes
  capa_raised, a notification appears (bell), and an email is sent to the assignee.
- Open the CAPA → closing is blocked until the effectiveness check is recorded.
- Create a risk → score and matrix shown. Attach evidence to any record.

---

## Generic reference (any environment)

## One-time first deployment (Milestone 1.1)

**1. Get the production salt**
On the server, read the CakePHP salt:
```
flinkiso app -> app/Config/core.php -> Configure::write('Security.salt', '....')
```
Copy that value for the `.env` below.

**2. Put the code on the server**
Clone/upload `flinkiso-laravel-api/` into the subdomain folder (GitHub SSH preferred).
Do not upload `vendor/`, `.env`, `node_modules/`, or `public/vendor` build artifacts you
do not need. `public/vendor/flinkiso` (the theme assets) IS required.

**3. Install dependencies**
```bash
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

**4. Configure `.env`**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://qms.flinkiso.dctrd.us

# Primary QMS database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=qmsdb
DB_USERNAME=qmsdb
DB_PASSWORD=8m&XtvkD3te?rq9V

# Legacy FlinkISO database (read only)
FLINKISO_DB_HOST=127.0.0.1
FLINKISO_DB_PORT=3306
FLINKISO_DB_DATABASE=flinkisodb
FLINKISO_DB_USERNAME=flinkisodb
FLINKISO_DB_PASSWORD=?*QfebQVppbhh746

# Auth bridge
FLINKISO_SECURITY_SALT=<<production CakePHP Security.salt from step 1>>
JWT_SECRET=<<generate a long random string>>
JWT_TTL_MINUTES=120

# No infra tables in the DB
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```
Remove the `DB_SOCKET` / `FLINKISO_DB_SOCKET` lines if the server connects over TCP
(most Plesk setups do); keep them only if MySQL uses a unix socket.

**5. Create the QMS tables (in qmsdb)**
```bash
php artisan migrate --force
php artisan db:seed --class=QmsDemoSeeder --force   # optional sample document
```

**6. Permissions + production optimisation**
```bash
chmod -R ug+rw storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**7. Test Milestone 1.1**
- Open https://qms.flinkiso.dctrd.us/api/health  -> both `qms_db` and `legacy_db` connected.
- Open https://qms.flinkiso.dctrd.us/login  -> sign in with a FlinkISO account.
- Run the flow: create document, review, approve, release, export PDF, raise + implement a
  change request, issue a controlled copy, check the audit trail.

## Redeploying for later milestones (weeks 2, 3, 4)

Each new milestone only adds code + new `qms_*` migrations, so redeploy is simple:
```bash
git pull                                   # or upload changed files
composer install --no-dev --optimize-autoloader
php artisan migrate --force                # applies new qms_* migrations only
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
No changes to CakePHP or `flinkisodb` are needed for these.

## Rollback

- Code: `git checkout <previous tag/commit>` then re-run composer + cache steps.
- Database: `qms_*` migrations are additive. To undo the last batch: `php artisan migrate:rollback`.
- The legacy `flinkisodb` is never modified, so the client's live app is never at risk.

## Important notes

- We do NOT run the MyISAM->InnoDB migration on the live `flinkisodb`; Milestone 1.x only
  reads it. That hardening is done later, with a backup and client approval, only if a
  module needs Laravel to write to legacy tables.
- Keep `.env`, `DEPLOY-CREDENTIALS.txt`, and the salt out of git.
- Point the subdomain document root at `public/`, never at the app root.
