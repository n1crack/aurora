# Aurora Shopping Cart for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ozdemir/aurora.svg?style=flat-square)](https://packagist.org/packages/ozdemir/aurora)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/ozdemir/aurora/run-tests?label=tests)](https://github.com/ozdemir/aurora/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/ozdemir/aurora/Check%20&%20fix%20styling?label=code%20style)](https://github.com/ozdemir/aurora/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ozdemir/aurora.svg?style=flat-square)](https://packagist.org/packages/ozdemir/aurora)

The Aurora Shopping Cart for Laravel package provides a convenient way to manage a shopping cart in your Laravel applications. It allows you to add, update, remove items, apply conditions, and calculate the total and subtotal amounts. 
## Support us

..

## Installation

You can install the package via composer:

```bash
composer require ozdemir/aurora
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="aurora-config"
```

This is the contents of the published config file:

```php
<?php

return [

    'instance' => 'cart',

    'storage' => \Ozdemir\Aurora\Storage\SessionStorage::class,

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

### Methods
- add($data): Adds an item or items to the cart.
- update($key, $data): Updates an item in the cart.
- items(): Returns the collection of items in the cart.
- exists($key): Checks if an item exists in the cart.
- item($key): Retrieves an item from the cart by its key.
- remove($key): Removes an item from the cart.
- isEmpty(): Checks if the cart is empty.
- total(): Calculates the total amount of the cart, including conditions.
- subtotal(): Calculates the subtotal amount of the cart without conditions.
- quantity(): Calculates the total quantity of items in the cart.
- weight(): Calculates the total weight of the items in the cart.
- clear(): Clears the cart and removes all items.
- sync($data): Clears the cart and adds new items.
- condition($condition): Adds a condition to the cart.
- hasCondition($name): Checks if a condition exists in the cart.
- removeCondition($name): Removes a condition from the cart.
- itemConditions($type = null): Retrieves the item-level conditions in the cart.
- conditions($type = null): Retrieves the cart-level conditions.
- getConditionsOrder(): Gets the order of cart-level conditions.
- getItemConditionsOrder(): Gets the order of item-level conditions.
- setConditionsOrder(array $conditionsOrder): Sets the order of cart-level conditions.
- setItemConditionsOrder(array $itemConditionsOrder, $updateExisting = true): Sets the order of item-level conditions.
- serialize(): Serializes the cart instance.
- unserialize($string): Unserializes a serialized cart instance.

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
