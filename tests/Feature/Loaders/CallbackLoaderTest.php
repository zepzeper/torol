<?php

namespace Tests\Feature\Loaders;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can load pipeline data into a variable using a callback', function () {
    $sourceData = [
        ['id' => 10, 'status' => 'processed'],
        ['id' => 20, 'status' => 'processed'],
    ];

    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            // Add each processed row to our results array.
            $results[] = $row->toArray();
        }));

    $this->assertCount(2, $results);
    $this->assertEquals(10, $results[0]['id']);
    $this->assertEquals('processed', $results[1]['status']);
});
