<?php

/**
 * Pure PHP via cURL + SOCKS5
 *
 * Env:
 *   IRAN_SOCKS5_PROXY=socks5h://user:pass@host:port
 * or separate:
 *   IRAN_SOCKS5_HOST / PORT / USER / PASS
 */

function iran_socks5_proxy_url(): string
{
    $ready = getenv('IRAN_SOCKS5_PROXY');
    if (is_string($ready) && $ready !== '') {
        return $ready;
    }

    $host = getenv('IRAN_SOCKS5_HOST') ?: '';
    $port = getenv('IRAN_SOCKS5_PORT') ?: '1080';
    $user = rawurlencode((string) (getenv('IRAN_SOCKS5_USER') ?: ''));
    $pass = rawurlencode((string) (getenv('IRAN_SOCKS5_PASS') ?: ''));

    if ($host === '' || $user === '' || $pass === '') {
        throw new RuntimeException('Set IRAN_SOCKS5_PROXY or IRAN_SOCKS5_HOST/USER/PASS');
    }

    return "socks5h://{$user}:{$pass}@{$host}:{$port}";
}

function iran_http(string $method, string $url, ?array $json = null, array $headers = []): string
{
    $ch = curl_init($url);
    $hdrs = array_merge(['Accept: application/json'], $headers);

    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $hdrs,
        CURLOPT_PROXY => iran_socks5_proxy_url(),
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 15,
    ];

    if ($json !== null) {
        $opts[CURLOPT_POSTFIELDS] = json_encode($json, JSON_UNESCAPED_UNICODE);
        $hdrs[] = 'Content-Type: application/json';
        $opts[CURLOPT_HTTPHEADER] = $hdrs;
    }

    curl_setopt_array($ch, $opts);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) {
        throw new RuntimeException("cURL error {$errno}: {$error}");
    }

    if ($status >= 400) {
        throw new RuntimeException("HTTP {$status}: {$body}");
    }

    return $body;
}

// Demo
if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
    echo iran_http('GET', 'https://ipinfo.io/json'), PHP_EOL;
}
