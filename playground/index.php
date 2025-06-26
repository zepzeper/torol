<?php

require_once "../vendor/autoload.php";

use Torol\Extractors\ArrayExtractor;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;


$sourceData = [
	['id' => '1', 'price' => '99.99', 'is_active' => '1', 'is_featured' => 'false'],
];

$results = [];

Pipeline::from(new ArrayExtractor($sourceData))
	->cast('id', 'int')
	->cast('price', 'float')
	->cast('is_active', 'bool')
	->cast('is_featured', 'boolean') // Alias for bool
    ->load(new CallbackLoader(function (Row $row) use (&$results) {
        $results[] = $row->toArray();
    }));

