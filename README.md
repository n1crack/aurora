# Laravel Shopping Cart

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ozdemir/laravel-cart.svg?style=flat-square)](https://packagist.org/packages/ozdemir/laravel-cart)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ozdemir/laravel-cart/run-tests?label=tests)](https://github.com/ozdemir/laravel-cart/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ozdemir/laravel-cart/Check%20&%20fix%20styling?label=code%20style)](https://github.com/ozdemir/laravel-cart/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ozdemir/laravel-cart.svg?style=flat-square)](https://packagist.org/packages/ozdemir/laravel-cart)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

..

## Installation

You can install the package via composer:

```bash
composer require ozdemir/laravel-cart
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-cart-config"
```

This is the contents of the published config file:

```php
<?php

return [

    'instance' => 'cart',

    'storage' => \Ozdemir\Cart\Storage\SessionStorage::class,

];
```

## Usage

```php
Cart::add([
    'id'       => 'some-product',
    'name'     => 'Some Product',
    'quantity' => 1,
    'price'    => 15,
]);

$items = Cart::items(); // list of the items in the Cart

echo Cart::total();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Yusuf Özdemir](https://github.com/n1crack)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
