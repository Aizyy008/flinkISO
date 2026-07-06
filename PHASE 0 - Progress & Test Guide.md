# Phase 0 — Progress & Test Guide

Tracking the foundation work. Rule: **extend/harden without breaking anything.**

| Task | Status |
|------|--------|
| 0.1 Run existing CakePHP app locally | ✅ Done (app + login working) |
| 0.2 Full DB backup + MyISAM → InnoDB migration | ✅ **Done & verified** |
| 0.3 Laravel API service skeleton (shared DB) | ⏳ Next |
| 0.4 Auth bridge / SSO (CakePHP ↔ Laravel) | ⏳ Pending |
| 0.5 Table-ownership map + conventions | ⏳ Pending |

---

## ✅ 0.2 — MyISAM → InnoDB Migration (completed)

### What was done
1. **Full backup taken first** (safety net) — verified it restores into a temp DB cleanly.
   Location: `project_1/backups/flinkiso-backup-YYYYMMDD-HHMMSS.sql`
2. **Pre-checks:** no FULLTEXT indexes, all 49 tables have primary keys → safe to migrate.
3. **Migrated all 49 tables** to `ENGINE=InnoDB, ROW_FORMAT=DYNAMIC`.
4. **Verified:** row counts identical before/after (zero data loss); app reads + writes work.

### Why it matters
InnoDB gives us **transactions, foreign keys, and crash recovery** — required for:
- Safe concurrent access when the new **Laravel API** writes to the same DB.
- **FDA 21 CFR Part 11** audit-trail integrity (tamper-evident records).

### Result
- **49/49 tables InnoDB**, 0 MyISAM left.
- No data lost. App fully functional.

### Rollback (if ever needed)
```bash
/opt/lampp/bin/mysql -u root < "/home/dev/Documents/noor projects/project_1/backups/flinkiso-backup-<STAMP>.sql"
```

---

## 🧪 How to test it yourself (browser)

> Make sure the app server is running (see `SETUP & RUN GUIDE.md` → Step 2) and log in:
> **http://127.0.0.1:8765/users/login** — `admin@flinkiso.local` / `admin@flinkiso.local`

### A. Confirm the app still works (read)
1. Open **http://127.0.0.1:8765/users/dashboard** — dashboard loads.
2. Visit a few modules and confirm each lists data / opens without error:
   - Documents: http://127.0.0.1:8765/qc_documents
   - Standards: http://127.0.0.1:8765/standards
   - Clauses: http://127.0.0.1:8765/clauses
   - Custom Forms: http://127.0.0.1:8765/custom_tables
   - Audit Schedule: http://127.0.0.1:8765/tbl_audit_schedule_0_v0s
   - Calibration: http://127.0.0.1:8765/tbl_calibration_0_v0s
   - Employees: http://127.0.0.1:8765/employees
   - Users: http://127.0.0.1:8765/users

### B. Confirm writes work (create)
3. Go to **Departments** → Add a department (any name) → Save.
   It should save and appear in the list. (You can delete it after.)
4. Optionally add a **Document Category** or a **Standard** the same way.

### C. Confirm the migration itself (technical, optional)
Run in a terminal — should show **InnoDB 49** and **no MyISAM**:
```bash
/opt/lampp/bin/mysql -u root -e "SELECT engine, COUNT(*) FROM information_schema.tables WHERE table_schema='flinkiso' GROUP BY engine;"
```

✅ If the pages load, and you can add a department, the migration is good and nothing broke.

---

## What I already tested (so you don't have to trust blindly)
- Logged in via the app; crawled **24 module pages** — all load (the only 404s are
  `/schedules` and `/settings`, which simply have no `index` page — pre-existing, not
  caused by the migration).
- Created a department through the app form → saved correctly (writes OK).
- Confirmed audit-history rows are still written during browsing (write path OK).
- Compared row counts before/after → **identical, zero data loss.**
