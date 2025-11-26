<?php

namespace Cloudmazing\KvkApi;

use GuzzleHttp\ClientInterface;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use stdClass;
use Cloudmazing\KvkApi\Company\Company;
use Cloudmazing\KvkApi\Exceptions\KvkApiException;

class Client implements KvkApiClientInterface {
    private const ENDPOINTS = [
        'SEARCH' => 'v2/zoeken',
        'BASE_PROFILE' => 'v1/basisprofielen',
    ];
    private const RATE_LIMIT = 0.2; // seconds between requests

    private ClientInterface $httpClient;
    private string $baseUrl;
    /** @var array<Company> */
    private array $results = [];
    private int $page = 1;
    private int $resultsPerPage = 10;
    private array $cache = [];
    private ?float $lastRequestTime = null;

    public function __construct(ClientInterface $httpClient) {
        $this->httpClient = $httpClient;
        $this->baseUrl = 'https://api.kvk.nl/api/';
    }

    /**
     * Search companies by name
     *
     * @param array<string, mixed> $params Additional search parameters
     * @return array<Company>
     * @throws KvkApiException
     */
    public function search(string $search, array $params = []): array {
        $this->results = []; // Reset results for new search

        $queryParams = array_merge([
            'pagina' => $this->page,
            'resultatenPerPagina' => $this->resultsPerPage,
        ], $params);

        if ($search !== '') {
            $queryParams['naam'] = $search;
        }

        try {
            $data = $this->getData($queryParams);
            $parsedData = $this->parseData($this->decodeJson($data));

            foreach ($parsedData as $item) {
                $data = $this->decodeJson($this->getRelatedData($item));
                $this->validateResponse($data);

                $this->results[] = new Company(
                    $data->kvkNummer ?? '',
                    $data->vestigingsnummer ?? null,
                    $data->naam ?? null,
                    $data->adres ?? null,
                    $data->websites ?? null
                );
            }

            return $this->results;
        } catch (\Exception $e) {
            throw new KvkApiException(
                "Failed to search companies: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Get company base profile by KvK number
     *
     * @throws KvkApiException
     */
    public function getBaseProfile(string $kvkNumber): Company {
        try {
            $url = $this->baseUrl . self::ENDPOINTS['BASE_PROFILE'] . '/' . $kvkNumber . '/hoofdvestiging';
            $response = $this->getCachedOrFetch($url);
            $data = $this->decodeJson($response);

            $this->validateResponse($data);

            return new Company(
                $data->kvkNummer ?? '',
                $data->vestigingsnummer ?? null,
                $data->eersteHandelsnaam ?? $data->handelsnaam ?? $data->naam ?? null,
                $this->formatAddresses($data->adressen ?? null),
                $data->websites ?? null
            );
        } catch (\Exception $e) {
            throw new KvkApiException(
                "Failed to fetch base profile: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByKvkNumber(string $kvkNumber, array $params = []): array {
        return $this->search('', array_merge(['kvkNummer' => $kvkNumber], $params));
    }

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByRsin(string $rsin, array $params = []): array {
        return $this->search('', array_merge(['rsin' => $rsin], $params));
    }

    /**
     * @param array<string, mixed> $params
     * @return array<Company>
     * @throws KvkApiException
     */
    public function searchByVestigingsnummer(string $vestigingsnummer, array $params = []): array {
        return $this->search('', array_merge(['vestigingsnummer' => $vestigingsnummer], $params));
    }

    public function setPage(int $page): self {
        $this->page = $page;
        return $this;
    }

    public function setResultsPerPage(int $resultsPerPage): self {
        $this->resultsPerPage = $resultsPerPage;
        return $this;
    }

    /**
     * @param array<string, mixed> $params
     * @throws KvkApiException
     */
    private function getData(array $params): string {
        try {
            $url = $this->baseUrl . self::ENDPOINTS['SEARCH'] . '?' . http_build_query($params);
            return $this->getCachedOrFetch($url);
        } catch (\Exception $e) {
            throw new KvkApiException(
                "Failed to fetch data from KVK API: {$e->getMessage()}",
                $e->getCode(),
                $e
            );
        }
    }

    private function getCachedOrFetch(string $url): string {
        if (isset($this->cache[$url])) {
            return $this->cache[$url];
        }

        $this->respectRateLimit(); // Voeg rate limiting toe voor elke nieuwe request
        $response = $this->httpClient->request('GET', $url);
        $data = $this->getJson($response);
        $this->cache[$url] = $data;

        return $data;
    }

    private function getJson(ResponseInterface $response): string {
        return (string) $response->getBody()->getContents();
    }

    /**
     * @return stdClass
     */
    private function decodeJson(string $json): stdClass {
        $data = json_decode($json, false);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new KvkApiException('Invalid JSON response: ' . json_last_error_msg());
        }
        return $data ?: new stdClass();
    }

    /**
     * Format addresses from KVK API response
     *
     * @param array<mixed>|null $addresses Raw address data from API
     * @return array<\stdClass>|null Formatted address objects
     * @throws \InvalidArgumentException When address data is malformed
     */
    private function formatAddresses(?array $addresses): ?array {
        if ($addresses === null) {
            return null;
        }

        return array_map(function ($address) {
            return (object) [
                'type' => $address->type ?? null,
                'straatnaam' => $address->straatnaam ?? null,
                'huisnummer' => $address->huisnummer ?? null,
                'postcode' => $address->postcode ?? null,
                'plaats' => $address->plaats ?? null,
                'land' => $address->land ?? null,
            ];
        }, $addresses);
    }

    /**
     * @return array<int, stdClass>
     */
    private function parseData(stdClass $data): array {
        $resultaten = $data->resultaten ?? [];
        /** @var array<int, stdClass> $resultatenArray */
        $resultatenArray = is_array($resultaten) ? $resultaten : [];

        return array_map(function ($value) {
            $value = (object) $value;
            /** @var array<string, mixed> $attributes */
            $attributes = array_diff_key((array) $value, array_flip(['type', 'links']));
            $value->attributes = $attributes;
            $value->id = uniqid();

            if (isset($value->links)) {
                /** @var array<stdClass> $links */
                $links = $value->links;
                /** @var array<string, string> $mappedLinks */
                $mappedLinks = array_column($links, 'href', 'rel');
                $value->links = $mappedLinks;
            } else {
                /** @var array<string, string> $emptyLinks */
                $emptyLinks = [];
                $value->links = $emptyLinks;
            }

            $value->actief = $value->actief ?? null;
            $value->vervallenNaam = $value->vervallenNaam ?? null;

            return $value;
        }, $resultatenArray);
    }

    private function getRelatedData(stdClass $parsedData): string {
        $relatedData = [];

        /** @var Collection<string, string> $links */
        $links = collect((array)($parsedData->links ?? []));

        $links->each(function (string $link) use (&$relatedData) {
            $response = $this->getCachedOrFetch($link);
            $data = $this->decodeJson($response);
            $relatedData = array_merge($relatedData, (array) $data);
        });

        return json_encode($relatedData) ?: '{}';
    }

    private function validateResponse(stdClass $data): void {
        if (!isset($data->kvkNummer)) {
            throw new KvkApiException('Invalid response: missing kvkNummer');
        }
    }

    private function respectRateLimit(): void {
        if ($this->lastRequestTime !== null) {
            $elapsed = microtime(true) - $this->lastRequestTime;
            if ($elapsed < self::RATE_LIMIT) {
                $sleepTime = (self::RATE_LIMIT - $elapsed);
                sleep($sleepTime);
            }
        }
        $this->lastRequestTime = microtime(true);
    }
}
