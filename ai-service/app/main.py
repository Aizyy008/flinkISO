"""
FlinkISO QMS — AI microservice (Milestone 2.2).

FastAPI service exposing risk scoring, predictive KPIs, CAPA suggestions and
HACCP anomaly detection. Bearer-token auth (AI_SERVICE_TOKEN); /health is open.
Run:  uvicorn app.main:app --host 0.0.0.0 --port 8100
"""
from __future__ import annotations

import os
from typing import Optional

from fastapi import Depends, FastAPI, Header, HTTPException
from pydantic import BaseModel, Field

from . import engine

app = FastAPI(title="FlinkISO QMS AI Service", version="1.0.0")


def require_token(authorization: Optional[str] = Header(default=None)) -> None:
    expected = os.getenv("AI_SERVICE_TOKEN", "").strip()
    if not expected:
        return  # unset = open (dev only); set it in staging/production
    if authorization != f"Bearer {expected}":
        raise HTTPException(status_code=401, detail="Invalid or missing bearer token.")


# ----- request models ----- #
class RiskIn(BaseModel):
    likelihood: int = Field(ge=1, le=5)
    severity: int = Field(ge=1, le=5)
    detection: int = Field(ge=1, le=5)
    context: Optional[str] = None


class Point(BaseModel):
    period: Optional[str] = None
    value: float


class KpiIn(BaseModel):
    history: list[Point]
    target: Optional[float] = None
    direction: str = "higher_better"


class CapaIn(BaseModel):
    title: str
    description: str = ""
    type: str = "non_conformity"
    severity: str = "medium"


class Reading(BaseModel):
    value: Optional[float] = None
    time: Optional[str] = None


class HaccpIn(BaseModel):
    readings: list[Reading]
    limit_min: Optional[float] = None
    limit_max: Optional[float] = None


# ----- routes ----- #
@app.get("/health")
def health() -> dict:
    return {"ok": True, "service": "flinkiso-ai", "version": app.version,
            "openai": bool(os.getenv("OPENAI_API_KEY"))}


@app.post("/ai/risk-score", dependencies=[Depends(require_token)])
def risk(inp: RiskIn) -> dict:
    return engine.risk_score(inp.likelihood, inp.severity, inp.detection, inp.context)


@app.post("/ai/kpi-forecast", dependencies=[Depends(require_token)])
def kpi(inp: KpiIn) -> dict:
    return engine.kpi_forecast([p.model_dump() for p in inp.history], inp.target, inp.direction)


@app.post("/ai/capa-suggest", dependencies=[Depends(require_token)])
def capa(inp: CapaIn) -> dict:
    return engine.capa_suggest(inp.title, inp.description, inp.type, inp.severity)


@app.post("/ai/haccp-anomaly", dependencies=[Depends(require_token)])
def haccp(inp: HaccpIn) -> dict:
    return engine.haccp_anomaly([r.model_dump() for r in inp.readings], inp.limit_min, inp.limit_max)
