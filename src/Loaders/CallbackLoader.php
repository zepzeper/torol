<?php

namespace Torol\Loaders;

use Torol\Contracts\LoaderInterface;
use Traversable;

class CallbackLoader implements LoaderInterface
{
    /**
     * @param callable $callback The function to call for each processed row.
     */
    public function __construct(private $callback)
    {
    }

    /**
     * Consumes the iterator from the pipeline and executes the callback for each row.
     *
     * This is where the pipeline's lazy execution is finally triggered. The foreach
     * loop pulls data through the entire chain of generators.
     *
     * @param Traversable<Row> $rows
     */
    public function load(Traversable $rows): int
    {
		$rowCount = 0;

        foreach ($rows as $row) {
            call_user_func($this->callback, $row);
			$rowCount++;
        }

		return $rowCount;
    }
}
