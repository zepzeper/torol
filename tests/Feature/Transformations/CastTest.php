<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can cast column values to different types', function () {
    $sourceData = [
        ['id' => '1', 'price' => '99.99', 'is_active' => '1', 'is_featured' => 'false'],
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->cast('id', 'int')
        ->cast('price', 'float')
        ->cast('is_active', 'bool')
        ->cast('is_featured', 'boolean') // Alias for bool
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(1, $results);
    $this->assertIsInt($results[0]['id']);
    $this->assertIsFloat($results[0]['price']);
    $this->assertIsBool($results[0]['is_active']);
    $this->assertTrue($results[0]['is_active']);
    $this->assertIsBool($results[0]['is_featured']);
    $this->assertFalse($results[0]['is_featured']);
});
