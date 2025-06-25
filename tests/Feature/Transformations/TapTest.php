<?php

namespace Tests\Feature\Transformations;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

it('can tap into the pipeline without modifying the row', function () {
    $sourceData = [['id' => 1, 'name' => 'Test']];
    $tappedData = null;
    $finalResult = [];

    Pipeline::from(new ArrayExtractor($sourceData))
        ->tap(function (Row $row) use (&$tappedData) {
            // Store a clone of the row as it exists at this point
            $tappedData = clone $row;
            // Modify the original row inside the tap to prove it doesn't affect the final result
            $row->set('name', 'Modified');
        })
        ->load(new CallbackLoader(function (Row $row) use (&$finalResult) {
            $finalResult[] = $row->toArray();
        }));

    $this->assertNotNull($tappedData);
    $this->assertEquals('Test', $tappedData->get('name'));
    $this->assertCount(1, $finalResult);
    $this->assertEquals('Test', $finalResult[0]['name'], 'The tap callback should not modify the pipeline data.');
});
