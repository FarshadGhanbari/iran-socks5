"""
Python requests via Iran SOCKS5

pip install "requests[socks]"
# or: pip install requests PySocks
"""

from __future__ import annotations

import os
from typing import Any
from urllib.parse import quote

import requests


def proxy_url() -> str:
    ready = os.getenv("IRAN_SOCKS5_PROXY")
    if ready:
        return ready

    host = os.environ["IRAN_SOCKS5_HOST"]
    port = os.getenv("IRAN_SOCKS5_PORT", "1080")
    user = quote(os.environ["IRAN_SOCKS5_USER"], safe="")
    password = quote(os.environ["IRAN_SOCKS5_PASS"], safe="")
    return f"socks5h://{user}:{password}@{host}:{port}"


def iran_session(timeout: int = 30) -> requests.Session:
    proxy = proxy_url()
    session = requests.Session()
    session.proxies.update({"http": proxy, "https": proxy})
    session.headers.update({"Accept": "application/json"})
    session.request = _with_timeout(session.request, timeout)  # type: ignore[method-assign]
    return session


def _with_timeout(request_fn, timeout: int):
    def wrapped(method, url, **kwargs):
        kwargs.setdefault("timeout", timeout)
        return request_fn(method, url, **kwargs)

    return wrapped


def get_json(url: str, **kwargs: Any) -> Any:
    with iran_session() as session:
        response = session.get(url, **kwargs)
        response.raise_for_status()
        return response.json()


def post_json(url: str, payload: dict[str, Any], **kwargs: Any) -> Any:
    with iran_session() as session:
        response = session.post(url, json=payload, **kwargs)
        response.raise_for_status()
        return response.json()


if __name__ == "__main__":
    print(get_json("https://ipinfo.io/json"))
