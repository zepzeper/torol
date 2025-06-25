<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('only allows rows with unique values in a column to pass', function () {
    $sourceData = [
        ['email' => 'one@example.com'],
        ['email' => 'two@example.com'],
        ['email' => 'one@example.com'], // Duplicate
        ['email' => 'three@example.com'],
        ['email' => 'two@example.com'], // Duplicate
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->unique('email')
        ->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(3, $results);
    $this->assertEquals('one@example.com', $results[0]['email']);
    $this->assertEquals('two@example.com', $results[1]['email']);
    $this->assertEquals('three@example.com', $results[2]['email']);
});
