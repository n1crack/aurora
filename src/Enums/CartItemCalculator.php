<?php

namespace Ozdemir\Aurora\Enums;

enum CartItemCalculator: string
{
    case PRICE = 'item.price';

    case SUBTOTAL = 'item.subtotal';
}
