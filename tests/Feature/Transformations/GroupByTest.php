<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Pipeline;
use Torol\Row;

it('can group rows by a specific column value', function () {
    $sourceData = [
        ['product' => 'Laptop', 'region' => 'North'],
        ['product' => 'Mouse', 'region' => 'South'],
        ['product' => 'Laptop', 'region' => 'South'],
        ['product' => 'Keyboard', 'region' => 'North'],
    ];

    // This is a terminal operation, it returns an array, not the pipeline.
    $groupedByRegion = Pipeline::from(new ArrayExtractor($sourceData))
        ->groupBy('region');

    $this->assertIsArray($groupedByRegion);
    $this->assertCount(2, $groupedByRegion, 'Should be grouped into 2 regions.');
    $this->assertArrayHasKey('North', $groupedByRegion);
    $this->assertArrayHasKey('South', $groupedByRegion);

    $this->assertCount(2, $groupedByRegion['North']);
    $this->assertCount(2, $groupedByRegion['South']);

    // Check one of the rows to ensure it's a Row object
    $this->assertInstanceOf(Row::class, $groupedByRegion['North'][0]);
    $this->assertEquals('Laptop', $groupedByRegion['North'][0]->get('product'));
});
