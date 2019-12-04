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
  - [TokenBuilder reference]
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
  - [Token (eloquent) reference]
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