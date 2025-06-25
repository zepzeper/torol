<?php

namespace Tests\Feature;

use Torol\Pipeline;
use Torol\Extractors\CsvExtractor;
use Torol\Loaders\JsonLoader;

it('can process a csv file and save it as json', function () {
    $sourceFile = __DIR__ . '/fixtures/users.csv';
    $outputFile = __DIR__ . '/output/users.json';

    // Ensure the output directory exists and is empty
    if (!is_dir(__DIR__ . '/output')) {
        mkdir(__DIR__ . '/output');
    }
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    Pipeline::from(new CsvExtractor($sourceFile))->load(new JsonLoader($outputFile));

    $this->assertFileExists($outputFile);

    $jsonContents = file_get_contents($outputFile);
    $data = json_decode($jsonContents, true);

    $this->assertCount(2, $data);
    $this->assertEquals('Eva', $data[0]['name']);
    $this->assertEquals('frank@example.com', $data[1]['email']);

    // Cleanup
    unlink($outputFile);
});
