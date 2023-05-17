<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Ozdemir\Aurora\Traits\CollectionArrayAccess;

class CartAttribute extends Collection
{
    use CollectionArrayAccess;

    public function __construct($items = [])
    {
        parent::__construct($items);
    }
}
