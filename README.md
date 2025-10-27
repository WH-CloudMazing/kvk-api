# KvK API Client (Dutch Chamber of Commerce)

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cloudmazing/kvk-api.svg?style=flat-square)](https://packagist.org/packages/cloudmazing/kvk-api)
[![Tests](https://github.com/WH-CloudMazing/kvk-api/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/WH-CloudMazing/kvk-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/cloudmazing/kvk-api.svg?style=flat-square)](https://packagist.org/packages/cloudmazing/kvk-api)

A robust PHP package for interacting with the Dutch Chamber of Commerce (KvK) API. This package is a fork of `vormkracht10/kvk-api` with additional features and improvements.

## Features

- Search companies by name, KvK number, RSIN, or establishment number
- Fetch detailed company profiles
- Built-in caching to minimize API calls
- Rate limiting to respect API guidelines
- Comprehensive error handling
- PSR-7 compliant

## Data Available

For each company, you can retrieve:
- KvK number
- Establishment number
- Trade name(s)
- Address(es) including:
  - Type
  - Full address
  - Street
  - House number
  - Postal code
  - City
  - Country
- Website(s)
- Basic profile information (new)

## Installation

Install the package via Composer:

```bash
composer require cloudmazing/kvk-api
```

Required dependencies:
```bash
composer require illuminate/support
composer require psr/http-message
composer require guzzlehttp/guzzle
```

## Getting Started

First, obtain your API key from the [KvK Developer Portal](https://developers.kvk.nl/).

```php
use Mantix\KvkApi\ClientFactory;

$apiKey = '<YOUR_KVK_API_KEY>';
$kvk = ClientFactory::create($apiKey);
```

### Basic Company Search

```php
// Search by company name
$companies = $kvk->search('Mantix');

// Search with pagination
$kvk->setPage(1)
    ->setResultsPerPage(10);
$companies = $kvk->search('Mantix');
```

### Specific Searches

```php
// By KvK number
$companies = $kvk->searchByKvkNumber('12345678');

// By RSIN
$companies = $kvk->searchByRsin('123456789');

// By establishment number
$companies = $kvk->searchByVestigingsnummer('000012345678');
```

### Get Company Profile

```php
// Fetch detailed company profile
$profile = $kvk->getBaseProfile('12345678');
```

### Advanced Usage

```php
// Search with additional parameters
$companies = $kvk->search('Mantix', [
    'pagina' => 1,
    'resultatenPerPagina' => 10,
    // Add any other API parameters
]);

// Using SSL certificate
$rootCertificate = '<PATH_TO_SSL_CERT>';
$kvk = ClientFactory::create($apiKey, $rootCertificate);
```

## Error Handling

The package includes comprehensive error handling:

```php
use Mantix\KvkApi\Exceptions\KvkApiException;

try {
    $companies = $kvk->search('Mantix');
} catch (KvkApiException $e) {
    // Handle API-specific errors
    echo $e->getMessage();
} catch (\Exception $e) {
    // Handle general errors
    echo $e->getMessage();
}
```

## Testing

```bash
composer test
```

## Documentation

- [Upgrade Guide](docs/upgrade.md)
- [Changelog](CHANGELOG.md)
- [KvK API Documentation](https://developers.kvk.nl/documentation)

## Contributing

Contributions are welcome! Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Original package by Vormkracht10](https://github.com/vormkracht10/kvk-api)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
