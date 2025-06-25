<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can remove a single column', function () {
    $sourceData = [['id' => 1, 'name' => 'Test', 'comment' => 'Extra']];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->removeColumn('comment')
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(1, $results);
    $this->assertArrayNotHasKey('comment', $results[0]);
    $this->assertArrayHasKey('name', $results[0]);
});

it('can remove multiple columns', function () {
    $sourceData = [['id' => 1, 'name' => 'Test', 'comment' => 'Extra', 'status' => 'old']];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->removeColumn(['comment', 'status'])
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(1, $results);
    $this->assertArrayNotHasKey('comment', $results[0]);
    $this->assertArrayNotHasKey('status', $results[0]);
    $this->assertCount(2, $results[0]);
});
