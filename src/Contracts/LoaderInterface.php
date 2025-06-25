<?php

namespace Torol\Contracts;

use Traversable;
use Zepzeper\Torol\Row;

/**
 * Defines the contract for any class that loads data into a destination.
 *
 * A Loader is responsible for the 'L' in ETL. Its single responsibility
 * is to receive an iterable of Row objects from the pipeline and write
 * them to a destination (like a file, database, or API).
 */
interface LoaderInterface
{
    /**
     * Loads the processed data into the target destination.
     *
     * @param Traversable<Row> $rows A generator of processed Row objects from the pipeline.
     * @return int Total number of rows processed.
     */
    public function load(Traversable $rows): int;
}
