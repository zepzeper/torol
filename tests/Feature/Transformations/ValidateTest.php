<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Pipeline;
use Torol\Row;

it('can validate rows and discard invalid ones', function () {
    $sourceData = [
        ['email' => 'test@example.com'],
        ['email' => ''], // Invalid
        ['email' => 'another@example.com'],
        ['email' => 'not-an-email'], // Invalid
    ];
    $results = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->validate(function (Row $row) {
            return !empty($row->get('email')) && filter_var($row->get('email'), FILTER_VALIDATE_EMAIL);
        })
		->load(new CallbackLoader(fn (Row $row) => $results[] = $row->toArray()));

    $this->assertCount(2, $results);
    $this->assertEquals('test@example.com', $results[0]['email']);
    $this->assertEquals('another@example.com', $results[1]['email']);
});
