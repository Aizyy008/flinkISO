"""
Core AI/analytics engine for the FlinkISO QMS AI microservice (Milestone 2.2).

Every function derives its output from the caller's inputs — no canned/mock
text. CAPA suggestions use OpenAI when a key is configured, and otherwise a
deterministic, input-driven rule engine so the endpoint is never "mock".
"""
from __future__ import annotations

import os
import re
import statistics
from typing import Optional

import httpx


# --------------------------------------------------------------------------- #
# 1. Risk scoring — likelihood x severity x detection (FMEA-style RPN)
# --------------------------------------------------------------------------- #
def risk_score(likelihood: int, severity: int, detection: int, context: Optional[str] = None) -> dict:
    likelihood = max(1, min(5, likelihood))
    severity = max(1, min(5, severity))
    detection = max(1, min(5, detection))
    rpn = likelihood * severity * detection  # 1..125

    if rpn >= 100 or severity == 5:
        band, priority = "critical", "Immediate action — escalate and contain now"
    elif rpn >= 64:
        band, priority = "high", "Plan corrective action within days"
    elif rpn >= 28:
        band, priority = "medium", "Schedule action; monitor"
    else:
        band, priority = "low", "Accept with periodic review"

    drivers = []
    if severity >= 4:
        drivers.append("high severity")
    if likelihood >= 4:
        drivers.append("high likelihood")
    if detection >= 4:
        drivers.append("poor detectability")
    rationale = (
        f"RPN {rpn} = likelihood {likelihood} x severity {severity} x detection {detection}"
        + (f"; driven by {', '.join(drivers)}" if drivers else "")
        + (f". Context: {context}" if context else "")
    )
    return {"rpn": rpn, "band": band, "priority": priority, "rationale": rationale}


# --------------------------------------------------------------------------- #
# 2. KPI forecast — least-squares trend on the result history
# --------------------------------------------------------------------------- #
def kpi_forecast(history: list[dict], target: Optional[float] = None,
                 direction: str = "higher_better") -> dict:
    values = [float(h["value"]) for h in history if h.get("value") is not None]
    n = len(values)
    if n == 0:
        return {"forecast": None, "trend": "no_data", "confidence": 0.0,
                "breach_risk": "unknown", "rationale": "No history supplied."}
    if n == 1:
        return {"forecast": round(values[0], 4), "trend": "insufficient_data",
                "confidence": 0.2, "breach_risk": "unknown",
                "rationale": "Only one data point; forecast = last value."}

    xs = list(range(n))
    mean_x, mean_y = statistics.mean(xs), statistics.mean(values)
    sxx = sum((x - mean_x) ** 2 for x in xs)
    sxy = sum((xs[i] - mean_x) * (values[i] - mean_y) for i in range(n))
    slope = sxy / sxx if sxx else 0.0
    intercept = mean_y - slope * mean_x
    forecast = slope * n + intercept  # next period

    # R^2 as a confidence proxy
    ss_tot = sum((v - mean_y) ** 2 for v in values)
    ss_res = sum((values[i] - (slope * xs[i] + intercept)) ** 2 for i in range(n))
    r2 = 1 - (ss_res / ss_tot) if ss_tot else 1.0
    confidence = round(max(0.0, min(1.0, r2)), 3)

    improving = slope > 0 if direction == "higher_better" else slope < 0
    if abs(slope) < (abs(mean_y) * 0.01 or 1e-9):
        trend = "stable"
    else:
        trend = "improving" if improving else "declining"

    breach_risk = "unknown"
    if target is not None:
        if direction == "higher_better":
            breach_risk = "high" if forecast < target else "low"
        else:
            breach_risk = "high" if forecast > target else "low"

    return {
        "forecast": round(forecast, 4),
        "trend": trend,
        "slope": round(slope, 4),
        "confidence": confidence,
        "breach_risk": breach_risk,
        "rationale": f"Fitted {n} periods, slope {round(slope,4)}/period, R^2 {confidence}. "
                     f"Next-period forecast {round(forecast,4)}"
                     + (f" vs target {target} -> breach risk {breach_risk}." if target is not None else "."),
    }


# --------------------------------------------------------------------------- #
# 3. HACCP anomaly detection — limit breaches + statistical outliers + trend
# --------------------------------------------------------------------------- #
def haccp_anomaly(readings: list[dict], limit_min: Optional[float] = None,
                  limit_max: Optional[float] = None) -> dict:
    vals = [float(r["value"]) for r in readings if r.get("value") is not None]
    if not vals:
        return {"anomalies": [], "summary": "No readings supplied.", "trend": "no_data"}

    mean = statistics.mean(vals)
    sd = statistics.pstdev(vals) if len(vals) > 1 else 0.0
    anomalies = []
    for i, r in enumerate(readings):
        v = r.get("value")
        if v is None:
            continue
        v = float(v)
        reasons = []
        if limit_min is not None and v < limit_min:
            reasons.append(f"below critical limit {limit_min}")
        if limit_max is not None and v > limit_max:
            reasons.append(f"above critical limit {limit_max}")
        if sd > 0 and abs(v - mean) > 3 * sd:
            reasons.append("statistical outlier (>3 sigma)")
        if reasons:
            anomalies.append({"index": i, "value": v, "time": r.get("time"),
                              "reasons": reasons, "severity": "critical" if "limit" in " ".join(reasons) else "warning"})

    # simple drift warning: last 3 readings trending toward a limit
    trend = "stable"
    if len(vals) >= 3:
        d = vals[-1] - vals[-3]
        if limit_min is not None and vals[-1] > limit_min and d < 0:
            trend = "approaching lower limit"
        elif limit_max is not None and vals[-1] < limit_max and d > 0:
            trend = "approaching upper limit"

    summary = (f"{len(anomalies)} anomaly(ies) in {len(vals)} readings; "
               f"mean {round(mean,3)}, sd {round(sd,3)}. Trend: {trend}.")
    return {"anomalies": anomalies, "summary": summary, "trend": trend,
            "mean": round(mean, 3), "std_dev": round(sd, 3)}


# --------------------------------------------------------------------------- #
# 4. CAPA suggestions — OpenAI when configured, deterministic fallback otherwise
# --------------------------------------------------------------------------- #
_CAUSE_HINTS = {
    "temperature": "process temperature control / equipment calibration",
    "clean": "cleaning effectiveness / sanitation schedule",
    "contamin": "hygiene controls / raw-material handling",
    "calibrat": "instrument calibration drift",
    "train": "operator competency / training gap",
    "document": "outdated or unavailable controlled document",
    "supplier": "supplier / incoming-material quality",
    "leak": "equipment integrity / maintenance",
    "delay": "scheduling / resource availability",
    "label": "labelling / packaging control",
}


def capa_suggest(title: str, description: str = "", type_: str = "non_conformity",
                 severity: str = "medium") -> dict:
    text = f"{title}. {description}".strip()
    key = os.getenv("OPENAI_API_KEY", "").strip()
    if key:
        try:
            return _capa_via_openai(text, type_, severity, key)
        except Exception as exc:  # noqa: BLE001 — fall back, never fail the endpoint
            fallback = _capa_rule_based(text, type_, severity)
            fallback["engine"] = f"rule_based (openai_error: {type(exc).__name__})"
            return fallback
    return _capa_rule_based(text, type_, severity)


def _capa_rule_based(text: str, type_: str, severity: str) -> dict:
    low = text.lower()
    causes = [hint for kw, hint in _CAUSE_HINTS.items() if kw in low]
    if not causes:
        causes = ["insufficient process control", "human error"]
    corrective = [
        f"Contain the immediate issue described in: '{text[:120]}'",
        f"Investigate root cause — likely area: {causes[0]}",
        "Correct affected product/records and re-verify conformance",
    ]
    preventive = [
        f"Update the relevant procedure/work instruction to address {causes[0]}",
        "Add a verification check / control at the failure point",
        "Train responsible staff on the revised control",
    ]
    if severity in ("high", "critical"):
        corrective.insert(0, "Escalate to management and quarantine affected batches immediately")
    return {
        "engine": "rule_based",
        "root_cause_hypotheses": causes,
        "corrective_actions": corrective,
        "preventive_actions": preventive,
    }


def _capa_via_openai(text: str, type_: str, severity: str, key: str) -> dict:
    model = os.getenv("OPENAI_MODEL", "gpt-4o-mini")
    prompt = (
        "You are a quality management (ISO 9001/22000) CAPA assistant. "
        f"For this {type_} (severity: {severity}):\n\"{text}\"\n"
        "Return STRICT JSON with keys root_cause_hypotheses (array of strings), "
        "corrective_actions (array), preventive_actions (array). No prose."
    )
    resp = httpx.post(
        "https://api.openai.com/v1/chat/completions",
        headers={"Authorization": f"Bearer {key}", "Content-Type": "application/json"},
        json={
            "model": model,
            "messages": [{"role": "user", "content": prompt}],
            "temperature": 0.2,
            "response_format": {"type": "json_object"},
        },
        timeout=30,
    )
    resp.raise_for_status()
    import json
    content = resp.json()["choices"][0]["message"]["content"]
    data = json.loads(content)
    data["engine"] = f"openai:{model}"
    return data
