<?php

namespace Tests\Feature;

use Generator;
use Torol\Pipeline;
use Torol\Row;
use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;

// Test case: The most basic end-to-end pipeline
it('can extract from an array, map the data, and load it into a variable', function () {
	$sourceData = [
		['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
		['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com'],
	];

	$result = [];

	Pipeline::from(new ArrayExtractor($sourceData))
		->map(function (Row $row) {
			// Transform the data: uppercase the name
			return $row->set('name', strtoupper($row->get('name')));
		})
		->load(new CallbackLoader(function (Row $row) use (&$result) {
			// Load the transformed data into the result array
			$result[] = $row->toArray();
		}));

	$this->assertCount(2, $result, 'The final array should contain 2 items.');
	$this->assertEquals('ALICE', $result[0]['name'], 'The name in the first record should be uppercased.');
	$this->assertEquals('BOB', $result[1]['name'], 'The name in the second record should be uppercased.');
	$this->assertEquals(2, $result[1]['id'], 'The ID of the second record should be preserved.');
});


// Test case: Filtering data within the pipeline
it('can filter data based on a condition', function () {
	$sourceData = [
		['id' => 1, 'name' => 'Charlie', 'type' => 'user'],
		['id' => 2, 'name' => 'Admin Bot', 'type' => 'bot'],
		['id' => 3, 'name' => 'Denise', 'type' => 'user'],
	];

	$result = [];

	Pipeline::from(new ArrayExtractor($sourceData))
		->filter(function (Row $row) {
			// Only allow rows where the 'type' is 'user'
			return $row->get('type') === 'user';
		})
		->load(new CallbackLoader(function (Row $row) use (&$result) {
			$result[] = $row->toArray();
		}));

	$this->assertCount(2, $result, 'The final array should only contain the filtered items.');
	$this->assertEquals('Charlie', $result[0]['name']);
	$this->assertEquals('Denise', $result[1]['name']);
});


// Test case: Chaining multiple transformations
it('can chain multiple transformations like filter and map', function () {
	$sourceData = [
		['product' => 'Laptop', 'price' => 800],
		['product' => 'Mouse', 'price' => 40],
		['product' => 'Keyboard', 'price' => 120],
		['product' => 'Webcam', 'price' => 90],
	];

	$result = [];

	Pipeline::from(new ArrayExtractor($sourceData))
		->filter(fn (Row $row) => $row->get('price') > 100) // Keep items over 100
		->map(fn (Row $row) => $row->set('price_with_tax', $row->get('price') * 1.21)) // Add tax
		->load(new CallbackLoader(function (Row $row) use (&$result) {
			$result[] = $row->toArray();
		}));

	$this->assertCount(2, $result);
	$this->assertEquals('Laptop', $result[0]['product']);
	$this->assertEquals(968, $result[0]['price_with_tax']); // 800 * 1.21
	$this->assertEquals('Keyboard', $result[1]['product']);
	$this->assertEquals(145.2, $result[1]['price_with_tax']); // 120 * 1.21
});

// Test case: Using the get() method 
it('can return an iterable using the get method', function () {
    $sourceData = [['value' => 1], ['value' => 2], ['value' => 3]];

    $iterator = Pipeline::from(new ArrayExtractor($sourceData))
        ->map(fn (Row $row) => $row->set('value', $row->get('value') * 2))
        ->get();

    $this->assertInstanceOf(Generator::class, $iterator);

    // Consume the iterator to verify its contents
    $results = [];
    foreach($iterator as $row) {
        $results[] = $row->toArray();
    }

    $this->assertEquals(4, $results[1]['value']);
    $this->assertEquals(6, $results[2]['value']);
});
