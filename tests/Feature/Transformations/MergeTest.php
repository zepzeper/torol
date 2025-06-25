<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can enrich a pipeline by merging data from another source', function () {
    $ordersData = [
        ['order_id' => 'A1', 'user_id' => 101, 'amount' => 50],
        ['order_id' => 'A2', 'user_id' => 102, 'amount' => 75],
        ['order_id' => 'A3', 'user_id' => 101, 'amount' => 25],
    ];
    $usersData = [
        ['id' => 101, 'name' => 'Alice'],
        ['id' => 102, 'name' => 'Bob'],
    ];
    $ordersExtractor = new ArrayExtractor($ordersData);
    $usersExtractor = new ArrayExtractor($usersData);
    $results = [];

    Pipeline::from($ordersExtractor)
        ->merge($usersExtractor, 'user_id', 'id')
        ->load(new CallbackLoader(function (Row $row) use (&$results) {
            $results[] = $row->toArray();
        }));

    $this->assertCount(3, $results);
    $this->assertEquals('Alice', $results[0]['name']);
    $this->assertEquals('Bob', $results[1]['name']);
    $this->assertEquals('Alice', $results[2]['name']);
    $this->assertArrayHasKey('name', $results[0]);
});
