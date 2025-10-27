<?php

namespace Cloudmazing\KvkApi;

use Cloudmazing\KvkApi\Company\Company;
use Cloudmazing\KvkApi\Exceptions\KvkApiException;

interface KvkApiClientInterface {
    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function search(string $search, array $params = []): array;

    /**
     * @throws KvkApiException
     */
    public function getBaseProfile(string $kvkNumber): Company;

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByKvkNumber(string $kvkNumber, array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByRsin(string $rsin, array $params = []): array;

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByVestigingsnummer(string $vestigingsnummer, array $params = []): array;
}
