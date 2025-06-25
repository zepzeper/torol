<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('applies a transformation only when a condition is met', function () {
    $sourceData = [
        ['type' => 'user', 'name' => 'john'],
        ['type' => 'admin', 'name' => 'jane'],
        ['type' => 'user', 'name' => 'pete'],
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->when(
            fn (Row $row) => $row->get('type') === 'admin',
            fn (Row $row) => $row->set('name', strtoupper($row->get('name')))
        )
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(3, $results);
    $this->assertEquals('john', $results[0]['name']);
    $this->assertEquals('JANE', $results[1]['name']); // Was transformed
    $this->assertEquals('pete', $results[2]['name']);
});
