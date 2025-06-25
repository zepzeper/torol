<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can nest multiple columns into a single sub-array', function () {
    $sourceData = [
        ['id' => 1, 'street' => '123 Main St', 'city' => 'Anytown', 'country' => 'USA'],
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->nest('address', ['street', 'city', 'country'])
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(1, $results);
    $this->assertArrayHasKey('id', $results[0]);
    $this->assertArrayHasKey('address', $results[0]);
    $this->assertArrayNotHasKey('street', $results[0]);
    $this->assertIsArray($results[0]['address']);
    $this->assertCount(3, $results[0]['address']);
    $this->assertEquals('Anytown', $results[0]['address']['city']);
});
