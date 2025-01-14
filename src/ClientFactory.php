<?php

namespace Mantix\KvkApi;

use GuzzleHttp\Client;
use Mantix\KvkApi\Client as KvkApiClient;

class ClientFactory {
    public static function create(string $apiKey, ?string $rootCertificate = null): KvkApiClient {
        return new KvkApiClient(
            self::createHttpClient($apiKey, $rootCertificate)
        );
    }

    private static function createHttpClient(
        string $apiKey,
        ?string $rootCertificate = null
    ): Client {
        if ($rootCertificate === null) {
            return new Client([
                'headers' => [
                    'apikey' => $apiKey,
                ],
            ]);
        }

        return new Client([
            'headers' => [
                'apikey' => $apiKey,
            ],
            'verify' => $rootCertificate,
        ]);
    }
}
