<?php

namespace Tests\Feature;

use Torol\Extractors\JsonExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can extract data from a specific path in a json file', function () {
    if (!class_exists(JsonMachine::class)) {
        $this->markTestSkipped('The halaxa/json-machine package is not installed.');
    }

    $sourceFile = __DIR__ . '/fixtures/products.json';
    $results = [];

    // The second argument '/products' is a JSON Pointer that tells the
    // extractor where the array of items is located in the file.
    Pipeline::from(new JsonExtractor($sourceFile, '/products'))->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
    }));

    // 3. Assert
    $this->assertCount(2, $results);
    $this->assertEquals(101, $results[0]['id']);
    $this->assertEquals('Gadget', $results[1]['name']);
});
