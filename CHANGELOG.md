# Changelog

All notable changes to this project `mantix/kvk-api` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/), 
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-14

### About this fork
This package is a fork of `vormkracht10/kvk-api` and continues development under the namespace `Mantix/KvkApi`. We thank the original authors for their work and aim to maintain compatibility while adding new features.

### Added
- New `getBaseProfile()` method to fetch company base profiles
- Response caching to minimize API calls
- Rate limiting to respect API limits (0.2s between requests)
- Interface `KvkApiClientInterface` for better testability
- Better error handling with custom exceptions
- Response validation for API calls
- PHPDoc improvements and stricter type hints

### Changed
- Moved from `Vormkracht10` to `Mantix` namespace
- Refactored HTTP client implementation
- API endpoints moved to constants
- Results array now resets between searches
- Improved address formatting
- Enhanced error messages

### Fixed
- Memory usage in large result sets
- Proper null handling in responses
- Type safety in collection handling

## Credits
- Original package by [Vormkracht10](https://github.com/vormkracht10/kvk-api)
- Fork maintained by [Mantix](https://github.com/mantix)

## License
The MIT License (MIT). Please see [License File](LICENSE.md) for more information.