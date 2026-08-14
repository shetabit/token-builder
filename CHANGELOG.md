# Changelog

All Notable changes to `tokenable` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## Unreleased

### Added
- A test suite of 55 tests: unit tests for the builder, the token, the repository, the validation concern, the facade
  and the service provider, and feature tests that run the flows of the readme end to end against a database. It
  covers 98% of `src/`.
- GitHub Actions workflows running the test suite (PHP 8.4 and 8.5, Laravel 12 and 13, lowest and highest
  dependencies), the coding style check, the static analysis and the code coverage on every pull request and on every
  push to `master`. The coverage has to stay above 95%.
- A `Dockerfile` (with pcov, for coverage) and a `Makefile` to run the test suite and every check inside a container,
  so that no PHP installation is needed on the host.
- A `phpcs.xml.dist` ruleset, a `phpstan.neon.dist` configuration (level 7 with larastan) and a `rector.php`
  configuration, plus the `test-coverage`, `analyse`, `rector` and `ci` composer scripts.
- The `token-builder-migrations` publish tag, next to the `migrations` one the package has always used, and
  `Shetabit\TokenBuilder\Facade\TokenBuilder::SERVICE_NAME` for the name of the binding.
- The builder can be resolved and injected by its class name (`Shetabit\TokenBuilder\Builder`) as well.

### Changed
- **Breaking:** PHP 8.4 is now the minimum required version (was PHP 7.2), and only the last two major versions of
  Laravel are supported: `^12.0|^13.0` (was `^5.8` and up). `illuminate/database` — the home of the Eloquent model and
  of the migration of the package — is declared now.
- The package was modernized for PHP 8.4. Every parameter, return value and property of `src/` declares a type now,
  and nullable types are spelled `T|null`.
- **Breaking:** `RepositoryInterface::model()` and `RepositoryAbstract::model()` declare `string`, and the abstract
  repository declares the types of its other methods. `RepositoryAbstract::model()` was declared without a visibility
  modifier, which PSR-2 does not allow.
- `Builder::setExpireDate()` takes a `CarbonInterface`, so an immutable date is accepted as well.
- The migration of the package is an anonymous class, the way Laravel writes migrations since 8.x, and it is published
  with `publishesMigrations()`.
- `Token::scopeValid()` compares `max_usage_limit` with the number `0` instead of the string `'0'`, and uses
  `orWhereColumn()` instead of a raw fragment.

### Fixed
- **`Token`:** `expired_at` was declared in the `$dates` property, which Laravel 11 dropped — the expiration date of a
  token came back as a plain string. It is a `datetime` cast now. The generated token is cast to `string`, so a token
  that was just built answers the same way as one that was read back.
- **`TokenBuilder` facade:** a facade keeps the object it resolved, and a builder carries the token it is building, so
  the settings of a token leaked into the next one built in the same request:
  `TokenBuilder::setUsageLimit(1)->build()` followed by `TokenBuilder::build()` built two limited tokens. Every access
  to the facade starts a new builder now.
- **`Builder::create()`** ignored the length it was given and always built a token of the default length.
- **`Builder::build()`** now says what is wrong when the related model does not use the `HasTemporaryTokens` trait,
  instead of failing with an "undefined method temporaryTokens()". The same goes for `TokensRepository`.
- **`Token`** uses `SoftDeletes`. The migration has always created a `deleted_at` column, which nothing ever wrote to:
  a deleted token was gone for good. **Breaking:** `$token->delete()` marks it as deleted now, and a deleted token is
  no longer found — `Token::withTrashed()` reaches it.
- **`Builder::build()`** refuses a length it can not build a token of, instead of handing an impossible range to
  `random_int()`. A generated token of `n` digits can start with the lowest `n` digit number again: the range used to
  start one above it.

### Removed
- The Travis CI configuration (`.travis.yml`), replaced by GitHub Actions.
- The StyleCI configuration (`.styleci.yml`), replaced by the PHP_CodeSniffer workflow.
- The `branch-alias` of `composer.json`, which pointed at a 1.0 development branch that does not exist.

## Date - 2019-01-09

### Fixed
- Nothing

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing
