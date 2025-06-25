<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Pipeline;
use Torol\Row;

it('can reduce a pipeline to a single value', function () {
    $sourceData = [
        ['item' => 'Laptop', 'price' => 1200],
        ['item' => 'Mouse', 'price' => 50],
        ['item' => 'Keyboard', 'price' => 150],
    ];

    // Calculate the sum of the 'price' column for all rows.
    $totalPrice = Pipeline::from(new ArrayExtractor($sourceData))
        ->reduce(function ($accumulator, Row $row) {
            return $accumulator + $row->get('price');
        }, 0); // Start the accumulator at 0

    $this->assertEquals(1400, $totalPrice);
});
