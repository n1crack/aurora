# Aurora Shopping Cart for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ozdemir/aurora)](https://packagist.org/packages/ozdemir/aurora)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/n1crack/aurora/run-tests.yml)](https://github.com/n1crack/aurora/actions)
[![GitHub](https://img.shields.io/github/license/n1crack/aurora)](https://github.com/n1crack/aurora/blob/main/LICENSE.md)

Aurora Cart is a flexible and feature-rich shopping cart library for Laravel.

## Features

- **Cart Management**: Easily manage the shopping cart for both guest and authenticated users.
- **Item Addition**: Add items to the cart with quantity and options support.
- **Item Modification**: Adjust item quantity, remove items, and update options.
- **Calculators**: Implement custom calculators for shipping, tax, or any other additional costs.
- **MetaData**: Attach meta information to the entire cart or individual items.
- **Snapshot and Rollback**: Save and restore the cart state for scenarios like order creation.
- **Validation**: Validate the cart integrity using checksums.

## Installation

Install the package using Composer:

```bash
composer require ozdemir/aurora
```

## Configuration

To configure Aurora Cart, publish the configuration file:

```bash
php artisan vendor:publish --tag="aurora-config"
```
This will create a cart.php file in your config directory. Here's an example configuration file with explanations:


```php
// config/cart.php

use Ozdemir\Aurora\Cart;
use Ozdemir\Aurora\Generators\GenerateChecksum;
use Ozdemir\Aurora\Generators\GenerateSessionKey;
use Ozdemir\Aurora\Storages\SessionStorage;

return [
    'instance' => 'cart',

    'cart_class' => Cart::class,

    'storage' => SessionStorage::class,

    'cache_store' => env('CART_STORE', config('cache.default')),

    'monetary' => [
        'precision' => env('CART_CURRENCY_PRECISION', 2),
    ],

    'session_key_generator' => GenerateSessionKey::class,

    'checksum_generator' => GenerateChecksum::class,

    'calculate_using' => [
        // Custom calculators go here
    ],
];
```
## Basic Usage

```php
// Create a product class implementing the Sellable interface
class SellableProduct implements Sellable
{
    use SellableTrait;
}
// Adding an item to the cart
$product = new SellableProduct(); // Replace with your actual product model
$cartItem = new CartItem($product, quantity: 2);
Cart::add($cartItem);

// Retrieving cart information
$total = Cart::total();
$itemCount = Cart::count();
$items = Cart::items();
$itemQuantity = Cart::quantity(); // total quantity

// Adding an item with options to the cart
Cart::add(
       (new CartItem($product, quantity: 1))->withOption('color', 'blue')
                 ->withOption('material', 'metal', price: 5)
                 ->withOption('size', 'large', weight: 4)
         );

// Updating item quantity
Cart::update($cartItem->hash(), quantity: 3);

// Removing an item from the cart
Cart::remove($cartItem->hash());
```


 
Aurora Cart supports custom calculators for calculating totals. You can add your custom calculators to the config/cart.php file under the calculate_using key.

### Example
```php
return [
    // ...
    'calculate_using' => [
        // discounts etc..
        ShippingCalculator::class
        TaxCalculator::class
    ],
];
```
```php
class ShippingCalculator
{
    public function handle($payload, Closure $next)
    {
        [$price, $breakdowns] = $payload;
        
        $shippingPrice = Shipping::first()->amount;

        $price = $price + $shippingPrice;

        $breakdowns[] = [
            'type' => 'shipping',
            'amount' => $shippingPrice,
            // any additional values..
        ];

        return $next([$price, $breakdowns]);
    }
}
```

```php
class TaxCalculator
{
    public function handle($payload, Closure $next)
    {
        [$price, $breakdowns] = $payload;
        
        $taxPrice = Tax::first()->amount;

        $price = $price + $taxPrice;

        $breakdowns[] = [
            'type' => 'tax',
            'amount' => $taxPrice,
            // any additional values..
        ];

        return $next([$price, $breakdowns]);
    }
}
``` 
Now, your cart will use these custom calculators to calculate totals, including shipping and tax. Adjust the logic in the calculators based on your specific business requirements.

## Breakdowns
You can retrieve the breakdowns of your cart using the Cart::breakdowns() method. Breakdowns provide a detailed summary of how the total amount is calculated, including contributions from various components such as shipping, tax, and any custom calculators you've added.

Example

```php
$breakdowns = Cart::breakdowns();

// Output the breakdowns
print_r($breakdowns);
``` 
The breakdowns() method returns an array where each element represents a breakdown item. Each breakdown item typically includes a label and value, providing transparency into how different factors contribute to the overall total.

Here's a hypothetical example output:

```php
Array (
    [0] => Array (
        [label] => Shipping
        [value] => 10
    )
    [1] => Array (
        [label] => Tax
        [value] => 15
    )
    // Additional breakdown items based on your custom calculators
    // ...
)
```
Adjust the output format and contents as needed for your specific use case.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Yusuf Özdemir](https://github.com/n1crack)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
