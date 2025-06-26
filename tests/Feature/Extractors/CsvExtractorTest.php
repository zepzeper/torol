<?php

namespace Tests\Feature\Extractors;

use Torol\Extractors\CsvExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can extract data from a csv file with headers', function () {
    $sourceFile = __DIR__ . '/../fixtures/users.csv';
    $results = [];

    Pipeline::from(new CsvExtractor($sourceFile))
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(2, $results);
    $this->assertEquals('Eva', $results[0]['name']);
    $this->assertEquals('frank@example.com', $results[1]['email']);
    $this->assertArrayHasKey('id', $results[0]);
});
