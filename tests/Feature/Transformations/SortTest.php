<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can sort the pipeline by a column value', function () {
    $sourceData = [
        ['name' => 'Charlie', 'score' => 80],
        ['name' => 'Alice', 'score' => 95],
        ['name' => 'Bob', 'score' => 85],
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->sort('score', SORT_DESC) // Sort by score, highest first
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(3, $results);
    $this->assertEquals('Alice', $results[0]['name']);
    $this->assertEquals('Bob', $results[1]['name']);
    $this->assertEquals('Charlie', $results[2]['name']);
});
