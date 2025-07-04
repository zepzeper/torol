<?php

namespace Tests\Feature\Extractors;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can extract data from a simple array', function () {
    $sourceData = [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
    ];

    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(2, $results);
    $this->assertEquals('Alice', $results[0]['name']);
    $this->assertEquals(2, $results[1]['id']);
});
