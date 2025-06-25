<?php

namespace Torol\Extractors;

use Torol\Contracts\ExtractorInterface;
use Torol\Row;
use Traversable;

class ArrayExtractor implements ExtractorInterface
{
    /**
     * @param array<int, array<string, mixed>> $sourceArray The array to extract data from.
     */
    public function __construct(private array $sourceArray)
    {
    }

    /**
     * Loops over the source array and yields a new Row object for each item.
     *
     * @return Traversable<Row>
     */
    public function extract(): Traversable
    {
        foreach ($this->sourceArray as $item) {
            yield new Row($item);
        }
    }
}
