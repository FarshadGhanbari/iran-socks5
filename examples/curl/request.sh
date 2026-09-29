#!/usr/bin/env bash
# Quick SOCKS5 test
set -euo pipefail

HOST="${IRAN_SOCKS5_HOST:?set IRAN_SOCKS5_HOST}"
PORT="${IRAN_SOCKS5_PORT:-1080}"
USER="${IRAN_SOCKS5_USER:?set IRAN_SOCKS5_USER}"
PASS="${IRAN_SOCKS5_PASS:?set IRAN_SOCKS5_PASS}"

curl -sS -x "socks5h://${USER}:${PASS}@${HOST}:${PORT}" https://ipinfo.io/
echo
