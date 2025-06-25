<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can rename a column', function () {
    $sourceData = [
        ['user_id' => 1, 'user_email' => 'test@example.com'],
    ];

    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->renameColumn('user_email', 'email')
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(1, $results);
    $this->assertArrayHasKey('email', $results[0]);
    $this->assertArrayNotHasKey('user_email', $results[0]);
    $this->assertEquals('test@example.com', $results[0]['email']);
});
