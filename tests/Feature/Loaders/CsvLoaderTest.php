<?php

namespace Tests\Feature\Loaders;

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CsvLoader;
use Torol\Pipeline;


it('can write data to a csv file with headers', function () {
    $sourceData = [
        ['id' => 1, 'product' => 'Laptop', 'price' => 1200],
        ['id' => 2, 'product' => 'Mouse', 'price' => 50],
    ];

    $outputFile = __DIR__ . '/../output/report.csv';

    // Ensure the output directory exists and any old file is removed
    $outputDir = dirname($outputFile);
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
    }
    if (file_exists($outputFile)) {
        unlink($outputFile);
    }

    Pipeline::from(new ArrayExtractor($sourceData))
        ->load(new CsvLoader($outputFile));

    $this->assertFileExists($outputFile);

    $fileContents = file_get_contents($outputFile);
    $expectedContents = "id,product,price\n";
    $expectedContents .= "1,Laptop,1200\n";
    $expectedContents .= "2,Mouse,50\n";

    $this->assertEquals($expectedContents, $fileContents);

    unlink($outputFile);
});
