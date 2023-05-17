<?php

namespace Ozdemir\Aurora;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Ozdemir\Aurora\Traits\CollectionArrayAccess;

class CartAttribute extends Collection
{
    use CollectionArrayAccess;

    public function __construct($items = [])
    {
        $validator = Validator::make($items, [
            'label' => ['required'],
            'value' => ['required'],
            'price' => ['sometimes', 'numeric'],
            'weight' => ['sometimes', 'numeric'],
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        parent::__construct($items);
    }
}
