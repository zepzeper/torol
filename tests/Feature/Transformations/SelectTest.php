<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can select specific columns to keep', function () {
    $sourceData = [['id' => 1, 'name' => 'Test', 'comment' => 'Extra']];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->select(['id', 'name'])
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(1, $results);
    $this->assertCount(2, $results[0]);
    $this->assertArrayHasKey('id', $results[0]);
    $this->assertArrayHasKey('name', $results[0]);
    $this->assertArrayNotHasKey('comment', $results[0]);
});
