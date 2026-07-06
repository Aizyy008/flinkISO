# FlinkISO — Local Setup & Run Guide

How to start the existing FlinkISO (CakePHP 2.10) app on this machine.

- **App path:** `/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise`
- **App URL:** http://127.0.0.1:8765
- **Stack:** LAMPP (PHP 8.2.4 + MariaDB 10.4 + Apache) at `/opt/lampp`

---

## 🔑 Credentials

### Database (MySQL / MariaDB)
| Field | Value |
|-------|-------|
| Host | `localhost` (LAMPP socket `/opt/lampp/var/mysql/mysql.sock`) |
| Port | `3306` |
| Database | `flinkiso` |
| App DB user | `flinkiso` |
| App DB password | `flinkiso` |
| Root user | `root` |
| Root password | *(empty — LAMPP default)* |

> These app DB credentials are already set in
> `app/Config/database.php` → `$default`.

### FlinkISO app login
Admin account (already created). In FlinkISO the **password is the same as the email**.
| Field | Value |
|-------|-------|
| Login URL | http://127.0.0.1:8765/users/login |
| Username | `admin@flinkiso.local` |
| Password | `admin@flinkiso.local` |
| Company | FlinkISO Dev |

> **How this admin was created:** FlinkISO's normal `/users/register` calls an external
> flinkiso.com API to validate an "on-premise" email — it can't self-register offline.
> For local dev the admin was seeded through the app's own registration logic (via a
> base64 payload to `/users/register/...`), which built the full Company→Branch→
> Department→Employee→User chain correctly. No source files were left modified.

---

## ▶️ Start the project (every time)

### Step 1 — Start MySQL (needs your sudo password, once per reboot)
```bash
sudo /opt/lampp/lampp startmysql
```
Check it is up:
```bash
pgrep -a mysqld
```

### Step 2 — Start the app web server
> ⚠️ **Always pass the FULL absolute path to `router.php`.** A relative path like
> `"../router.php"` will crash the server ("Failed opening required '../router.php'")
> because PHP's built-in server resolves it per-request, not from your terminal folder.
>
> ⚠️ **The `-d session.save_path=...` flag is required for login to work.** PHP's default
> session folder (`/opt/lampp/temp/`) is root-owned and not writable, so without this
> flag sessions can't be saved and login silently fails. This points sessions at the
> app's own writable `tmp/sessions` folder.

```bash
/opt/lampp/bin/fuser -k 8765/tcp 2>/dev/null   # free the port if already used
cd "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/webroot"
/opt/lampp/bin/php -d session.save_path="/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/tmp/sessions" -S 127.0.0.1:8765 "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/router.php"
```
Leave this terminal open (the server runs here). To run it in the background instead:
```bash
cd "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/webroot"
nohup /opt/lampp/bin/php -d session.save_path="/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/tmp/sessions" -S 127.0.0.1:8765 "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/router.php" > /tmp/flinkiso-server.log 2>&1 &
```

### Step 3 — Open in browser
- Main app: **http://127.0.0.1:8765**
- First run (no users yet) auto-redirects to registration:
  **http://127.0.0.1:8765/users/register**

Register the first admin there, then log in.

---

## 🗄️ Database setup commands (already done — for reference / re-install)

If you ever need to recreate the DB from scratch:

### 1. Create database + dedicated app user
```bash
/opt/lampp/bin/mysql -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS flinkiso CHARACTER SET utf8 COLLATE utf8_general_ci;
CREATE USER IF NOT EXISTS 'flinkiso'@'localhost' IDENTIFIED BY 'flinkiso';
GRANT ALL PRIVILEGES ON flinkiso.* TO 'flinkiso'@'localhost';
FLUSH PRIVILEGES;
SQL
```

### 2. Import the schema (49 tables)
```bash
/opt/lampp/bin/mysql -u root flinkiso \
  < "/home/dev/Documents/noor projects/project_1/flinkiso/flinkiso-ver-2x-on-premise/app/webroot/schema/flinkiso-on-premise.sql"
```

### 3. Verify
```bash
/opt/lampp/bin/mysql -u root flinkiso -e \
  "SELECT COUNT(*) AS tables FROM information_schema.tables WHERE table_schema='flinkiso';"
```
Expected: `49`.

### Handy DB access
```bash
# As app user
/opt/lampp/bin/mysql -u flinkiso -pflinkiso flinkiso
# As root (empty password)
/opt/lampp/bin/mysql -u root flinkiso
```

---

## 🛑 Stop the server
```bash
/opt/lampp/bin/fuser -k 8765/tcp     # stops the app web server
sudo /opt/lampp/lampp stopmysql      # stops MySQL (optional)
```

---

## 🩺 Troubleshooting

| Problem | Fix |
|---------|-----|
| Browser shows nothing / connection refused | Server not running → redo **Step 2**. |
| `Access denied for user 'flinkiso'` | Re-run DB **step 1** (create user + grant). |
| CSS/JS not loading | Make sure you start with `../router.php` (not `index.php`), so static files are served. |
| `SQLSTATE... could not find driver` | Use LAMPP's PHP (`/opt/lampp/bin/php`), not system PHP. |
| Port 8765 already in use | `/opt/lampp/bin/fuser -k 8765/tcp` then restart. |
| MySQL won't start | `sudo /opt/lampp/lampp startmysql`; check `/opt/lampp/var/mysql/dev.err`. |

---

## 📌 Notes
- `app/router.php` was added **only** to let PHP's built-in dev server serve static
  assets. On the real server (Apache/Plesk) it is ignored — `.htaccess` handles routing.
- All 49 tables are currently **MyISAM**. Per client approval, these will be migrated
  to **InnoDB** in Phase 0 (needed for transactions, foreign keys, and FDA-grade audit
  trails in the hybrid CakePHP + Laravel setup).
