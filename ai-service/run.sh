#!/usr/bin/env bash
# Dev runner: creates a venv, installs deps, starts the service on :8100
set -e
cd "$(dirname "$0")"
python3 -m venv .venv
. .venv/bin/activate
pip install -q -r requirements.txt
[ -f .env ] || cp .env.example .env
set -a; . ./.env; set +a
exec uvicorn app.main:app --host 0.0.0.0 --port 8100
