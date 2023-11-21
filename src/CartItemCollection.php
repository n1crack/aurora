<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Contracts\CartItemInterface;
use Ozdemir\Aurora\Contracts\MoneyInterface;

class CartItemCollection extends Collection
{
    /**
     * @param string $key
     */
    public function get($key, $default = null): ?CartItem
    {
        return parent::get($key, $default);
    }

    public function updateOrAdd(CartItem $cartItem): CartItem
    {
        if ($this->has([$cartItem->hash()])) {
            // if the item is already exists increase quantity.
            $this->get($cartItem->hash())->increaseQuantity($cartItem->quantity);
        } else {
            // otherwise add a new item.
            $this->put($cartItem->hash(), $cartItem);
        }

        return $this->get($cartItem->hash());
    }

    public function subtotal(): MoneyInterface
    {
        $money = config('cart.monetary.class');

        return new $money(
            $this->sum(fn (CartItemInterface $cartItem) => $cartItem->subtotal()->amount())
        );
    }
}
