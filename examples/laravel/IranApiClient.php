<?php

namespace App\Services;

use App\Support\IranSocks5;

/**
 * Example service — replace URL/payload with your own endpoint.
 * Copy to: app/Services/IranApiClient.php
 */
class IranApiClient
{
    public function createItem(array $payload): array
    {
        $response = IranSocks5::http()
            ->post('https://api.example.ir/v1/items', $payload);

        $response->throw();

        return $response->json();
    }
}
