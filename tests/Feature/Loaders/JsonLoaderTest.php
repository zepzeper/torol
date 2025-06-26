<?php

namespace Tests\Feature\Loaders;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\JsonLoader;
use Torol\Pipeline;

it('can write data to a json file', function () {
    $sourceData = [
        ['id' => 1, 'product' => 'Glowstone'],
        ['id' => 2, 'product' => 'Redstone'],
    ];

    $outputFile = __DIR__ . '/../output/items.json';

    // Ensure the output directory exists and is empty
    $outputDir = dirname($outputFile);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    Pipeline::from(new ArrayExtractor($sourceData))
        ->load(new JsonLoader($outputFile, prettyPrint: false));

    $this->assertFileExists($outputFile);

    $jsonContents = file_get_contents($outputFile);
    $data = json_decode($jsonContents, true);

    $this->assertCount(2, $data);
    $this->assertEquals('Glowstone', $data[0]['product']);
    $this->assertEquals(2, $data[1]['id']);

    // Check that it's a compact JSON string when prettyPrint is false
    $expectedJson = '[{"id":1,"product":"Glowstone"},{"id":2,"product":"Redstone"}]';
    $this->assertEquals($expectedJson, $jsonContents);

    unlink($outputFile);
});
