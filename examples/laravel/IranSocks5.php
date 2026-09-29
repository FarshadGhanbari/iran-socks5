<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Laravel HTTP client via Iran SOCKS5
 * Copy to: app/Support/IranSocks5.php
 */
class IranSocks5
{
    public static function proxyUrl(): string
    {
        $host = config('services.iran_socks5.host');
        $port = config('services.iran_socks5.port');
        $user = rawurlencode((string) config('services.iran_socks5.user'));
        $pass = rawurlencode((string) config('services.iran_socks5.pass'));

        if (blank($host) || blank($user) || blank($pass)) {
            throw new \RuntimeException('Set IRAN_SOCKS5_* in .env');
        }

        return "socks5h://{$user}:{$pass}@{$host}:{$port}";
    }

    public static function http(int $timeout = 30): PendingRequest
    {
        return Http::withOptions([
            'proxy' => self::proxyUrl(),
            'curl' => [
                CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
            ],
            'timeout' => $timeout,
            'connect_timeout' => 15,
        ])->acceptJson();
    }
}
