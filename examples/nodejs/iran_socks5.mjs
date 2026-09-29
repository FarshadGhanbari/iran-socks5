/**
 * Node.js fetch via SOCKS5
 *
 * npm i socks-proxy-agent
 * # Node 18+ has global fetch
 */

import { SocksProxyAgent } from "socks-proxy-agent";

function proxyUrl() {
  if (process.env.IRAN_SOCKS5_PROXY) {
    return process.env.IRAN_SOCKS5_PROXY;
  }

  const host = process.env.IRAN_SOCKS5_HOST;
  const port = process.env.IRAN_SOCKS5_PORT || "1080";
  const user = encodeURIComponent(process.env.IRAN_SOCKS5_USER || "");
  const pass = encodeURIComponent(process.env.IRAN_SOCKS5_PASS || "");

  if (!host || !user || !pass) {
    throw new Error("Set IRAN_SOCKS5_PROXY or IRAN_SOCKS5_HOST/USER/PASS");
  }

  return `socks5h://${user}:${pass}@${host}:${port}`;
}

export function iranAgent() {
  return new SocksProxyAgent(proxyUrl());
}

export async function iranFetch(url, options = {}) {
  const agent = iranAgent();
  const res = await fetch(url, { ...options, agent });
  if (!res.ok) {
    throw new Error(`HTTP ${res.status}: ${await res.text()}`);
  }
  return res;
}

if (import.meta.url === `file://${process.argv[1]}`) {
  const res = await iranFetch("https://ipinfo.io/json");
  console.log(await res.json());
}
