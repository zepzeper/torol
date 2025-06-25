<?php

namespace Torol\Contracts;

use Traversable;
use Zepzeper\Torol\Row;

/**
 * Defines the contract for any class that extracts data from a source.
 *
 * An extractor is responsible for the 'E' in ETL. Its single responsibility
 * is to read from a source (like a file, database, or API) and YIELD each record
 * one by one in a memory efficient way.
 */
interface ExtractorInterface
{
    /**
     * Extracts data from the source and yields each record as a Row object.
     *
     * @return Traversable<Row> A generator that yields Row objects.
     */
    public function extract(): Traversable;
}
