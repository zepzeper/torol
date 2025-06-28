<?php

require_once "../vendor/autoload.php";

use Torol\Builder\ApiExtractorBuilder;
use Torol\Loaders\CallbackLoader;
use Torol\Pipeline;
use Torol\Row;

$builder = new ApiExtractorBuilder('https://dummyjson.com');
$builder->endpoint('/products')
    ->withDataKey('products')
    ->withOffsetPagination(10, 'limit', 'skip');

$stats = Pipeline::from($builder->build())
    ->onError(function(Throwable $e, $context) {
        echo "AN ERROR OCCURRED!\n";
        echo $e->getMessage() . "\n";
    })
    ->takeWhile(function(Row $row) {
        return $row->get('id') < 2;
    })
    ->tap(function(Row $row) {
        var_dump($row->toArray());
    })
    ->load(new CallbackLoader(function(Row $row) {
    }));

dd($stats);
