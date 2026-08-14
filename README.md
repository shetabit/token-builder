<p align="center"><img src="resources/images/laravel-token-builder-package.png?raw=true"></p>

# Laravel Token Builder

[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads on Packagist][ico-download]][link-packagist]
[![Tests][ico-tests]][link-tests]
[![Code Style][ico-code-style]][link-code-style]
[![Static Analysis][ico-static-analysis]][link-static-analysis]
[![Code Coverage][ico-coverage]][link-coverage]

This package requires `PHP 8.4+` and supports `Laravel 12` and `Laravel 13`.

This package can generate **random tokens** which **can expires**.

Generated token can be used for creating **one-time links**,
or **authentication with sms pin** and etc.

we have 2 things that can **expire** genereted tokens:

- **time limit** : for example a token can be expired after 2022/05/12
- **usage limit** : for example a token can be expired after using it more than 3 times.

[Donate me](https://yekpay.me/mahdikhanzadi) if you like this package :sunglasses: :bowtie:

# List of contents

- [Available drivers](#list-of-available-drivers)
- [Install](#install)
- [Configure](#configure)
- [How to use](#how-to-use)
  - [Generating tokens](#generating-tokens)
  - [Adding expiration date](#adding-expiration-date)
  - [Adding usage limit](#adding-usage-limit)
  - [Validation](#validation)
  - [Add relations](#useful-methods)
  - [Attach custom data](#useful-methods)
  - [Retrieve tokens](#retrieve-tokens)
  - [The valid tokens of a model](#the-valid-tokens-of-a-model)
  - [TokenBuilder reference](#tokenbuilder-reference)
  	- [setUniqueId](#setUniqueId)
  	- [getUniqueId](#getUniqueId)
  	- [setData](#setData)
  	- [getData](#getData)
  	- [setExpireDate](#setExpireDate)
  	- [getExpireDate](#getExpireDate)
  	- [setUsageLimit](#setUsageLimit)
  	- [getUsageLimit](#getUsageLimit)
  	- [setType](#setType)
  	- [getType](#getType)
  	- [setRelatedItem](#setRelatedItem)
  	- [getRelatedItem](#getRelatedItem)
  	- [build](#build)
  	- [findToken](#findToken)
  	- [findValidToken](#findValidToken)
  	- [findValidTokens](#findValidTokens)
  	- [hasAnyValidToken](#hasAnyValidToken)
  - [Model (HasTemporaryTokens) reference](#model-reference)
  	- [temporaryTokens](#temporaryTokens)
  	- [temporaryTokenBuilder](#temporaryTokenBuilder)
  	- [validTemporaryTokens](#validTemporaryTokens)
  	- [hasValidTemporaryToken](#hasValidTemporaryToken)
  - [Token (Eloquent) reference](#token-reference)
  	- [use](#use)
  	- [hasUsed](#hasUsed)
  	- [hasMaxUsageLimit](#hasMaxUsageLimit)
  	- [hasExpired](#hasExpired)
  	- [markAsExpired](#markAsExpired)
  	- [isValid](#isValid)
  	- [tokenable](#tokenable)
- [Testing](#testing)
- [Change log](#change-log)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

## Install

Via Composer

``` bash
$ composer require shetabit/token-builder
```

## Configure

The service provider and the `TokenBuilder` alias are registered by Laravel's package discovery, so there is nothing to
add to `config/app.php` (or to `bootstrap/providers.php`).

Run the below commands to **publish migrations** and **create tables**

```bash
php artisan vendor:publish --tag=token-builder-migrations

php artisan migrate
```

## How to use

you can use `TokenBuilder` facade or `Builder` class to build tokens. 

#### Generating tokens

In your code, use **facade** like the below :

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;

// ...

$tokenObject = $token = TokenBuilder::build();
```

you can also use `Builder` :

```php
use Shetabit\TokenBuilder\Builder;

// ...

$builder = new Builder;

$tokenObject = $builder->build();
```

each **tokenObject** has a **unique value** that can be used to recognize it from others and we call it `token`.
you can access to tokenObject unique token using `token`, see below example:

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;

// ...

// generate and store token in database
$tokenObject = $token = TokenBuilder::build();

// show token unique value
echo $tokenObject->token;
```

you can retrieve token and send it to users via email or sms or using it in URLs.
it can be used to **sms verifications** or **one-time login pins** , etc.

#### Adding expiration date

you can build a token with auto expiration date.
this kind of token will be expired after the specified date.

```php
    use Shetabit\TokenBuilder\Facade\TokenBuilder;

    // ...

    // will be expired after 5 minutes
    $date = Carbon::now()->addMinutes(5);
    $tokenObject = TokenBuilder::setExpireDate($date)->build();

    echo $tokenObject->token; // show unique token
    echo $tokenObject->expired_at; // show expiration datetime

    if ($tokenObject->hasExpired()) {
        echo 'token has expired';
    }
```

#### Adding usage limit

you can add usage limit into your tokens.
a token will be invalid after the usage has exceeded the limit.

```php
    use Shetabit\TokenBuilder\Facade\TokenBuilder;

    // ...

    // will be expired after 1 usage.
    $tokenObject = TokenBuilder::setUsageLimit(1)->build();

    // use token --> increment the usage counter
    $tokenObject->use();

    echo $tokenObject->token; // show unique token
    echo $tokenObject->usage_count; // show total usages count
    echo $tokenObject->max_usage_limit; // show max usage limit

    // determine if user has used
    if ($tokenObject->hasUsed()) {
        echo 'token has used';
    }

    // determin if token has exceed max usage
    if ($tokenObject->hasExceedMaxUsage()) {
        echo 'token used has exceeded the specified limit';
    }
```

#### Validation

you can validate a token using `isValid` method.
a token is valid if has not exceeded the specified limit and has not expired yet.

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;

// ...

$date = Carbon::now()->addMinutes(5); // will be expired after 5 minutes
$usageLimit = 1; // max usages
$token = TokenBuilder::setExpireDate($date)->setUsageLimit($usageLimit)->build();

if ($token->isValid()) {
    echo 'token is valid';

    $token->use(); // use token (increament usage counter)
} else {
    echo 'token is not valid any more!';
}
```

you can use a token by running `use` method.
you can expire a token by running `markAsExpired` method. <sub>(this method will update expiration date to current date)</sub>.

#### Add relations

you can add a relation to tokens. this can be done in 2 different ways:

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;
use App\User;

$user = User::first();

/**
 * first example: using TokenBuilder
 **/

$tokenObject = TokenBuilder::setRelatedItem($user)->build();

/**
 * second example: using a main model
 **/

$tokenObject = $user->temporaryTokenBuilder()->build();

// you can access to token's relation using the tokenable
dd($tokenObject->tokenable);

if ($tokenObject->tokenable) {
    echo $tokenObject->tokenable->email;
}

```

#### Attach custom data

you can attach custom data into your token. this data will be stored in json format.

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;

/**
 * first example: using TokenBuilder
 **/

$data = [
    'mobile' => '9373620353',
    'name' => 'John Doe',
];

$tokenObject = TokenBuilder::setData($data)->build();

echo $tokenObject->data['mobile'];

```

#### Retrieve tokens

you can retrieve `tokenObject` using token's unique string

```php
$token = 22325651; // your unique token

// retrieve token object
$tokenObject = TokenBuilder::setUniqueId($token)->findToken();

// retrieve token object if it is valid
$tokenObject = TokenBuilder::setUniqueId($token)->findValidToken();

// retrieve token object using its relation
$tokenObject = $user->temporaryTokenBuilder()->setUniqueId($token)->findToken();

// retrieve token object if it is valid using its relation
$tokenObject = $user->temporaryTokenBuilder()->setUniqueId($token)->findValidToken();
```

#### The valid tokens of a model

sometimes you don't have a token yet and want to know whether a model still has one that is valid:
a `forgot password` pin should not be sent again while the one that was sent is still usable.

a model that uses the `HasTemporaryTokens` trait can answer that itself:

```php
use App\User;

$user = User::first();

// does this user still have a valid token?
if ($user->hasValidTemporaryToken()) {
    echo 'this user has a valid token';
}

// the same question, for one type of token only
if ($user->hasValidTemporaryToken('reset-password')) {
    echo 'a reset pin was sent and is still valid';
}

// retrieve them, latest one first
$tokenObjects = $user->validTemporaryTokens('reset-password');

echo $tokenObjects->count();
echo $tokenObjects->first()?->token;
```

so a pin is only built when there is none left:

```php
if (!$user->hasValidTemporaryToken('reset-password')) {
    $user->temporaryTokenBuilder()
        ->setType('reset-password')
        ->setUsageLimit(1)
        ->setExpireDate(Carbon::now()->addMinutes(5))
        ->build(6);
}
```

the `TokenBuilder` answers the same questions, and without a relation it looks at the tokens of every model:

```php
use Shetabit\TokenBuilder\Facade\TokenBuilder;

// the valid tokens of one user, of one type
$tokenObjects = TokenBuilder::setRelatedItem($user)->setType('reset-password')->findValidTokens();

// is there any valid reset pin at all?
$exists = TokenBuilder::setType('reset-password')->hasAnyValidToken();
```

**notice:** `findValidTokens` and `hasAnyValidToken` ask whether there is *any* valid token, so they ignore
`setUniqueId`. use `findValidToken` or `isValid` to validate a token you have.

#### TokenBuilder reference

This is a reference for TokenBuilder methods.
`TokenBuilder` creates a tokenObject (instance of eloquent `Token` model) if you run build method and searches into data base using `findToken` and `findValidToken`. 

- ###### setUniqueId
- 
  you can build tokens with your custom algoritm and use it as unique id.
  
  ```php
    $token = 'jgaZ1z9';
    $tokenObject = TokenBuilder::setUniqueId($token)->build();
    echo $tokenObject->token; // jgaZ1z9
  ```

- ###### getUniqueId
- 
  returns your custom unique id (if not exists, returns `null`).

- ###### setData
- 
  you can attach some data into tokens and retrieve them later.

- ###### getData
  
  returns attached data.

- ###### setExpireDate
  
  sets an expiration date.

- ###### getExpireDate
  
  retrieves expiration date.

- ###### setUsageLimit
  
  sets usage limit.

- ###### getUsageLimit
  
  retrieves usage limit.

- ###### setType
  
  sets token type. you can set type for your tokens. its like a scope for your tokens.

- ###### getType
  
  retrieves token type.

- ###### setRelatedItem
  
  add a relation to token.

- ###### getRelatedItem
  
  retrieve current relation.

- ###### build
  
  generate a token. The number of digits of a generated token can be given: `build(6)` builds a six digit one, the
  default is eight. `create()` is an alias of it.

- ###### findToken
  
  find token (being valid or not valid is not important) and return `null` if not exists.

- ###### findValidToken
  
  find token if it is valid and return `null` if not exists.

- ###### findValidTokens

  retrieve every valid token of the related item (and of the type, when one is set), latest one first, as an eloquent
  collection. without a related item it looks at the tokens of every model. the unique id plays no part in it.

- ###### hasAnyValidToken

  determine if there is any valid token of the related item (and of the type, when one is set), and returns a boolean
  result. the unique id plays no part in it either.

#### Model Reference

these methods are added to your own models by the `Shetabit\TokenBuilder\Traits\HasTemporaryTokens` trait.

- ###### temporaryTokens

  the `morphMany` relation with every token of the model, valid or not.

- ###### temporaryTokenBuilder

  a `TokenBuilder` with the model set as the related item.

- ###### validTemporaryTokens

  retrieve the valid tokens of the model, latest one first, as an eloquent collection.
  a type can be given (`validTemporaryTokens('reset-password')`) to only look at the tokens of that type.

- ###### hasValidTemporaryToken

  determine if the model still has a valid token, and returns a boolean result.
  a type can be given here as well.

#### Token Reference

- ###### use
  
  add 1 to usage counter. (usageCounter = usageCounter + 1)

- ###### hasUsed
  
  determine is current token has used and returns a boolean result.

- ###### hasMaxUsageLimit
  
  determine if current token max usage is limited and returns a boolean result.

- ###### hasExpired
  
  determine if current token has expired or not.

- ###### markAsExpired

  mark current token as expired.
  
  **notice:** this method updates `expired_at` field to current time (now).

- ###### isValid

  determines if current token is valid (must not be expired and must not be exceeded max usage limit).

- ###### tokenable

  returns the token relation if there is any relation, and returns null if no relation exists.

## Testing

Every pull request and every push to `master` is checked by [GitHub Actions][link-actions]: the test suite runs on
PHP 8.4 and 8.5, against Laravel 12 and 13 and against both the lowest and the highest supported dependencies, the
coding style is checked with PHP_CodeSniffer, the sources are analysed with PHPStan (level 7, with larastan) and the
code coverage of the test suite is measured and has to stay above 95%.

The suite has two parts: `tests/Unit` covers the builder, the token, the repository, the token trait and the provider
on their own, and `tests/Feature` runs the flows of this readme end to end against a database.

You can run the same checks locally. With PHP and Composer installed on your machine:

```bash
composer install

composer test           # run the test suite
composer test-coverage  # run the test suite and report code coverage
composer check-style    # check the coding style
composer fix-style      # fix the coding style where possible
composer analyse        # run static analysis
composer ci             # run all of the checks above
```

If you would rather not install PHP on your machine, the shipped `Dockerfile` and `Makefile` run everything inside a
container:

```bash
make test              # run the test suite
make coverage          # run the test suite and report code coverage
make check-style       # check the coding style
make fix-style         # fix the coding style where possible
make analyse           # run static analysis
make ci                # run all of the checks above
make shell             # open a shell inside the container
make help              # list every available target
```

Another PHP version can be used with `make test PHP_VERSION=8.5`, and a single Laravel version with
`make test-laravel LARAVEL=12`.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email khanzadimahdi@gmail.com instead of using the issue tracker.

## Credits

- [Mahdi khanzadi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.



[ico-version]: https://img.shields.io/packagist/v/shetabit/token-builder.svg?style=flat-square
[ico-download]: https://img.shields.io/packagist/dt/shetabit/token-builder.svg?color=%23F18&style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-tests]: https://img.shields.io/github/actions/workflow/status/shetabit/token-builder/tests.yml?branch=master&label=Tests&style=flat-square
[ico-code-style]: https://img.shields.io/github/actions/workflow/status/shetabit/token-builder/code-style.yml?branch=master&label=Code%20Style&style=flat-square
[ico-static-analysis]: https://img.shields.io/github/actions/workflow/status/shetabit/token-builder/static-analysis.yml?branch=master&label=Static%20Analysis&style=flat-square
[ico-coverage]: https://img.shields.io/codecov/c/github/shetabit/token-builder/master?label=Coverage&style=flat-square

[link-packagist]: https://packagist.org/packages/shetabit/token-builder
[link-actions]: https://github.com/shetabit/token-builder/actions
[link-tests]: https://github.com/shetabit/token-builder/actions/workflows/tests.yml
[link-code-style]: https://github.com/shetabit/token-builder/actions/workflows/code-style.yml
[link-static-analysis]: https://github.com/shetabit/token-builder/actions/workflows/static-analysis.yml
[link-coverage]: https://codecov.io/gh/shetabit/token-builder
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors
