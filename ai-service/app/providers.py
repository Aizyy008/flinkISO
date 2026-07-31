"""
LLM provider abstraction for the FlinkISO QMS AI microservice.

The active provider is chosen at runtime by the ``AI_PROVIDER`` environment
variable and is fully replaceable without touching ``engine.py``. Every provider
implements one method — ``complete_json(system, user) -> str`` — and talks to the
vendor over plain HTTP (httpx), so no per-vendor SDK has to be installed.

Configure via environment variables:

  AI_PROVIDER = openai | azure | anthropic | gemini | ollama | none
                (unset  -> "openai" when OPENAI_API_KEY is present, else "none")

  openai     OPENAI_API_KEY, OPENAI_MODEL (gpt-4o-mini), OPENAI_BASE_URL
  azure      AZURE_OPENAI_API_KEY, AZURE_OPENAI_ENDPOINT, AZURE_OPENAI_DEPLOYMENT,
             AZURE_OPENAI_API_VERSION (2024-02-15-preview)
  anthropic  ANTHROPIC_API_KEY, ANTHROPIC_MODEL (claude-sonnet-4-5)
  gemini     GEMINI_API_KEY, GEMINI_MODEL (gemini-1.5-flash)
  ollama     OLLAMA_BASE_URL (http://localhost:11434), OLLAMA_MODEL (llama3.1)

Any provider that is selected but missing its config reports ``available() ==
False``, and the engine falls back to its deterministic rule-based output — the
endpoint never fails.
"""
from __future__ import annotations

import os
from typing import Optional

import httpx

_TIMEOUT = 30


class LLMProvider:
    """Common interface every provider implements."""

    name = "base"

    def available(self) -> bool:
        """True when this provider has the config it needs to be called."""
        return False

    def label(self) -> str:
        """Short identifier recorded on the response (e.g. 'openai:gpt-4o-mini')."""
        return self.name

    def complete_json(self, system: str, user: str) -> str:
        """Send the prompt and return the model's raw text (expected: JSON)."""
        raise NotImplementedError


class OpenAIProvider(LLMProvider):
    name = "openai"

    def __init__(self) -> None:
        self.key = os.getenv("OPENAI_API_KEY", "").strip()
        self.model = os.getenv("OPENAI_MODEL", "gpt-4o-mini").strip()
        self.base = os.getenv("OPENAI_BASE_URL", "https://api.openai.com/v1").rstrip("/")

    def available(self) -> bool:
        return bool(self.key)

    def label(self) -> str:
        return f"openai:{self.model}"

    def complete_json(self, system: str, user: str) -> str:
        resp = httpx.post(
            f"{self.base}/chat/completions",
            headers={"Authorization": f"Bearer {self.key}", "Content-Type": "application/json"},
            json={
                "model": self.model,
                "messages": [
                    {"role": "system", "content": system},
                    {"role": "user", "content": user},
                ],
                "temperature": 0.2,
                "response_format": {"type": "json_object"},
            },
            timeout=_TIMEOUT,
        )
        resp.raise_for_status()
        return resp.json()["choices"][0]["message"]["content"]


class AzureOpenAIProvider(LLMProvider):
    name = "azure"

    def __init__(self) -> None:
        self.key = os.getenv("AZURE_OPENAI_API_KEY", "").strip()
        self.endpoint = os.getenv("AZURE_OPENAI_ENDPOINT", "").rstrip("/")
        self.deployment = os.getenv("AZURE_OPENAI_DEPLOYMENT", "").strip()
        self.version = os.getenv("AZURE_OPENAI_API_VERSION", "2024-02-15-preview").strip()

    def available(self) -> bool:
        return bool(self.key and self.endpoint and self.deployment)

    def label(self) -> str:
        return f"azure:{self.deployment}"

    def complete_json(self, system: str, user: str) -> str:
        url = (
            f"{self.endpoint}/openai/deployments/{self.deployment}"
            f"/chat/completions?api-version={self.version}"
        )
        resp = httpx.post(
            url,
            headers={"api-key": self.key, "Content-Type": "application/json"},
            json={
                "messages": [
                    {"role": "system", "content": system},
                    {"role": "user", "content": user},
                ],
                "temperature": 0.2,
                "response_format": {"type": "json_object"},
            },
            timeout=_TIMEOUT,
        )
        resp.raise_for_status()
        return resp.json()["choices"][0]["message"]["content"]


class AnthropicProvider(LLMProvider):
    name = "anthropic"

    def __init__(self) -> None:
        self.key = os.getenv("ANTHROPIC_API_KEY", "").strip()
        self.model = os.getenv("ANTHROPIC_MODEL", "claude-sonnet-4-5").strip()

    def available(self) -> bool:
        return bool(self.key)

    def label(self) -> str:
        return f"anthropic:{self.model}"

    def complete_json(self, system: str, user: str) -> str:
        resp = httpx.post(
            "https://api.anthropic.com/v1/messages",
            headers={
                "x-api-key": self.key,
                "anthropic-version": "2023-06-01",
                "Content-Type": "application/json",
            },
            json={
                "model": self.model,
                "max_tokens": 1024,
                "temperature": 0.2,
                "system": f"{system} Respond with a single valid JSON object and nothing else.",
                "messages": [{"role": "user", "content": user}],
            },
            timeout=_TIMEOUT,
        )
        resp.raise_for_status()
        return resp.json()["content"][0]["text"]


class GeminiProvider(LLMProvider):
    name = "gemini"

    def __init__(self) -> None:
        self.key = os.getenv("GEMINI_API_KEY", "").strip()
        self.model = os.getenv("GEMINI_MODEL", "gemini-1.5-flash").strip()

    def available(self) -> bool:
        return bool(self.key)

    def label(self) -> str:
        return f"gemini:{self.model}"

    def complete_json(self, system: str, user: str) -> str:
        url = (
            f"https://generativelanguage.googleapis.com/v1beta/models/"
            f"{self.model}:generateContent?key={self.key}"
        )
        resp = httpx.post(
            url,
            headers={"Content-Type": "application/json"},
            json={
                "systemInstruction": {"parts": [{"text": system}]},
                "contents": [{"role": "user", "parts": [{"text": user}]}],
                "generationConfig": {"temperature": 0.2, "responseMimeType": "application/json"},
            },
            timeout=_TIMEOUT,
        )
        resp.raise_for_status()
        return resp.json()["candidates"][0]["content"]["parts"][0]["text"]


class OllamaProvider(LLMProvider):
    name = "ollama"

    def __init__(self) -> None:
        self.base = os.getenv("OLLAMA_BASE_URL", "http://localhost:11434").rstrip("/")
        self.model = os.getenv("OLLAMA_MODEL", "llama3.1").strip()

    def available(self) -> bool:
        return bool(self.base and self.model)

    def label(self) -> str:
        return f"ollama:{self.model}"

    def complete_json(self, system: str, user: str) -> str:
        resp = httpx.post(
            f"{self.base}/api/chat",
            json={
                "model": self.model,
                "messages": [
                    {"role": "system", "content": system},
                    {"role": "user", "content": user},
                ],
                "options": {"temperature": 0.2},
                "format": "json",
                "stream": False,
            },
            timeout=_TIMEOUT,
        )
        resp.raise_for_status()
        return resp.json()["message"]["content"]


class NoneProvider(LLMProvider):
    """Explicit 'no LLM' — the engine uses its rule-based output."""

    name = "none"


_REGISTRY = {
    "openai": OpenAIProvider,
    "azure": AzureOpenAIProvider,
    "anthropic": AnthropicProvider,
    "gemini": GeminiProvider,
    "ollama": OllamaProvider,
    "none": NoneProvider,
}


def get_provider(name: Optional[str] = None) -> LLMProvider:
    """
    Resolve the configured provider. ``AI_PROVIDER`` selects it; when unset we
    keep backward compatibility by using OpenAI if an OPENAI_API_KEY is present,
    otherwise 'none' (rule-based).
    """
    key = (name or os.getenv("AI_PROVIDER") or "").strip().lower()
    if not key:
        key = "openai" if os.getenv("OPENAI_API_KEY", "").strip() else "none"
    return _REGISTRY.get(key, NoneProvider)()
