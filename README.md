<p align="center"><img src="resources/images/laravel-token-builder-package.png?raw=true"></p>




# Laravel Token Builder




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
  - [Token (Eloquent) reference](#token-reference)
  	- [use](#use)
  	- [hasUsed](#hasUsed)
  	- [hasMaxUsageLimit](#hasMaxUsageLimit)
  	- [hasExpired](#hasExpired)
  	- [markAsExpired](#markAsExpired)
  	- [isValid](#isValid)
  	- [tokenable](#tokenable)
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

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias.

In your `config/app.php` file add these two lines.

```php
# In your providers array.
'providers' => [
    ...
    Shetabit\TokenBuilder\Provider\TokenBuilderServiceProvider::class,
],

# In your aliases array.
'aliases' => [
    ...
    'Payment' => Shetabit\TokenBuilder\Facade\TokenBuilder::class,
],
```

then, run the below commands to **publish migrations** and **create tables**

```bash
php artisan vendor:publish

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

#### TokenBuilder reference

- ###### setUniqueId
- ###### getUniqueId
- ###### setData
- ###### getData
- ###### setExpireDate
- ###### getExpireDate
- ###### setUsageLimit
- ###### getUsageLimit
- ###### setType
- ###### getType
- ###### setRelatedItem
- ###### getRelatedItem
- ###### build
- ###### findToken
- ###### findValidToken

#### Token Reference

- ###### use
- ###### hasUsed
- ###### hasMaxUsageLimit
- ###### hasExpired
- ###### markAsExpired
- ###### isValid
- ###### tokenable

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



[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors