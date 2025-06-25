<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can add a new column to the pipeline based on existing data', function () {
    $sourceData = [
        ['first_name' => 'John', 'last_name' => 'Doe'],
        ['first_name' => 'Jane', 'last_name' => 'Doe'],
    ];

    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->addColumn('full_name', function (Row $row) {
            return $row->get('first_name') . ' ' . $row->get('last_name');
        })
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(2, $results);
    $this->assertArrayHasKey('full_name', $results[0]);
    $this->assertEquals('John Doe', $results[0]['full_name']);
    $this->assertEquals('Jane Doe', $results[1]['full_name']);
});
