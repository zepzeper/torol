<?php

namespace Torol;

use Generator;
use stdClass;
use Throwable;
use Traversable;
use Torol\Contracts\ExtractorInterface;
use Torol\Contracts\LoaderInterface;
use Torol\Support\Stats;

class Pipeline
{
    private static $SKIP;

    private Traversable $iterator;

    /** @var callable|null $errorHandler */
    private $errorHandler = null;

    private function __construct(ExtractorInterface $extractor)
    {
        $this->iterator = $extractor->extract();
    }

    public static function from(ExtractorInterface $extractor): self
    {
        return new self($extractor);
    }

    public function onError(callable $handler): self
    {
        $this->errorHandler = $handler;
        return $this;
    }


    /**
     * Wraps the current iterator in a new generator that applies the map function.
     * @param callable $callback
     */
    public function map(callable $callback): self
    {
        return $this->pipe(fn(Row $row) => $callback($row));
    }

    /**
     * Wraps the current iterator in a new generator that applies the filter function.
     * @param callable $callback
     */
    public function filter(callable $callback): self
    {
        return $this->pipe(fn(Row $row) => $callback($row) ? $row : self::getSkip());
    }

    /**
     * Adds a new column to each row using a callback to generate the value.
     * @param callable $callback
     */
    public function addColumn(string $key, callable $callback): self
    {
        return $this->pipe(function (Row $row) use ($key, $callback) {
            return $row->set($key, $callback($row));
        });
    }

    /**
     * Renames a column from an old key to a new key.
     */
    public function renameColumn(string $from, string $to): self
    {
        return $this->pipe(function (Row $row) use ($from, $to) {
            $value = $row->get($from);
            return $row->remove($from)->set($to, $value);
        });
    }

    /**
     * Removes one or more columns from each row.
     *
     * @param string|array<string> $columns The column key or keys to remove.
     */
    public function removeColumn(string|array $columns): self
    {
        $keysToRemove = (array) $columns;
        return $this->pipe(function (Row $row) use ($keysToRemove) {
            foreach ($keysToRemove as $key) {
                $row->remove($key);
            }
            return $row;
        });
    }

    /**
     * Selects a subset of columns to keep, discarding all others.
     * @param string|array<string> $columns
     */
    public function select(string|array $columns): self
    {
        $keysToKeep = (array) $columns;
        return $this->pipe(function (Row $row) use ($keysToKeep) {
            $newData = [];
            foreach ($keysToKeep as $key) {
                if ($row->has($key)) {
                    $newData[$key] = $row->get($key);
                }
            }
            return new Row($newData);
        });
    }

    /**
     * Glorified with a new name. But same as filter.
     * @param callable $callback
     */
    public function validate(callable $callback): self
    {
        return $this->pipe(fn(Row $row) => $callback($row) ? $row : self::getSkip());
    }

    /**
     * Peek the pipeline.
     * @param callable $callback
     */
    public function tap(callable $callback): self
    {
        return $this->pipe(function (Row $row) use ($callback) {
            $callback($row);
            return $row;
        });
    }

    /**
     * Cast the value of a key.
     * @param string $key
     * @param string $type 'int', 'integer', 'string', 'bool', 'boolean', 'float', 'double'
     */
    public function cast(string $key, string $type): self
    {
        return $this->pipe(function (Row $row) use ($key, $type) {
            $value = $row->get($key);
            $castedValue = match (strtolower($type)) {
                'int', 'integer' => (int) $value,
                'string' => (string) $value,
                'bool', 'boolean' => (bool) $value,
                'float', 'double' => (float) $value,
                default => $value,
            };
            return $row->set($key, $castedValue);
        });
    }

    /**
     * Filters the pipeline to only include rows with a unique value in a given column.
     *
     * @param string $key The column key to check for uniqueness.
     */
    public function unique(string $key): self
    {
        $seenValues = [];
        return $this->pipe(function (Row $row) use ($key, &$seenValues) {
            $value = $row->get($key);
            if (isset($seenValues[$value])) {
                return self::getSkip();
            }
            $seenValues[$value] = true;
            return $row;
        });
    }

    /**
     * Applies a transformation only to rows that meet a specific condition.
     *
     * @param callable $condition A function that returns true if the transform should be applied.
     * @param callable $transformer A function that transforms the row.
     */
    public function when(callable $condition, callable $transformer): self
    {
        return $this->pipe(function (Row $row) use ($condition, $transformer) {
            return $condition($row) ? $transformer($row) : $row;
        });
    }

    /**
     * Nests a list of columns into a new sub-array under a single key.
     *
     * @param string $key The new key for the nested array (e.g., 'address').
     * @param array<string> $columnsToNest The list of column keys to move.
     */
    public function nest(string $key, array $columnsToNest): self
    {
        return $this->pipe(function (Row $row) use ($key, $columnsToNest) {
            $nestedData = [];
            foreach ($columnsToNest as $column) {
                if ($row->has($column)) {
                    $nestedData[$column] = $row->get($column);
                    $row->remove($column);
                }
            }
            return $row->set($key, $nestedData);
        });
    }

    /**
     * Merges data from a secondary source into the main pipeline (like a LEFT JOIN).
     *
     * @param ExtractorInterface $extractor The secondary data source to pull from.
     * @param string $localKey The key in the main pipeline's rows to join on.
     * @param string $foreignKey The key in the secondary source's rows to join on.
     */
    public function merge(ExtractorInterface $extractor, string $localKey, string $foreignKey): self
    {
        $previousIterator = $this->iterator;

        $mergeGenerator = function () use ($extractor, $localKey, $foreignKey, $previousIterator): Generator {
            $lookupMap = [];
            foreach ($extractor->extract() as $foreignRow) {
                $lookupMap[$foreignRow->get($foreignKey)] = $foreignRow->toArray();
            }

            foreach ($previousIterator as $localRow) {
                $joinValue = $localRow->get($localKey);
                if (isset($lookupMap[$joinValue])) {
                    $mergedData = array_merge($localRow->toArray(), $lookupMap[$joinValue]);
                    yield new Row($mergedData);
                } else {
                    yield $localRow;
                }
            }
        };

        $this->iterator = $mergeGenerator();
        return $this;
    }

    /**
     * Sorts the pipeline based on a specific column.
     * WARNING: This is a blocking, memory-intensive operation as it must
     * load the entire dataset into memory before sorting can occur.
     *
     * @param string $key The column key to sort by.
     * @param string|int $direction The sort direction, either SORT_ASC or SORT_DESC.
     */
    public function sort(string $key, string|int $direction = SORT_ASC): self
    {
        if (is_string($direction)) {
            $direction = match (strtoupper($direction)) {
                'DESC' => SORT_DESC,
                default => SORT_ASC,
            };
        }

        $allRows = iterator_to_array($this->iterator, false);
        usort($allRows, fn(Row $a, Row $b) => $a->get($key) <=> $b->get($key));

        if ($direction === SORT_DESC) {
            $allRows = array_reverse($allRows);
        }

        $this->iterator = (function () use ($allRows): Generator {
            yield from $allRows;
        })();

        return $this;
    }

    /**
     * Groups the rows by the value of a specific column.
     * Consumes the Iterator!
     *
     * @param string $key The column key to group by.
     * @return array<string, array<Row>> An associative array where keys are the unique
     * values from the column and values are arrays of Row objects.
     */
    public function groupBy(string $key): array
    {
        $groups = [];
        foreach ($this->iterator as $row) {
            $groupKey = $row->get($key);
            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [];
            }
            $groups[$groupKey][] = $row;
        }
        return $groups;
    }

    /**
     * Reduces the pipeline to a single value.
     *
     * @param callable $reducer The reducer function. It receives two arguments:
     * ($accumulator, Row $currentRow).
     * @param mixed $initialValue The initial value of the accumulator.
     * @return mixed The final value of the accumulator.
     */
    public function reduce(callable $reducer, mixed $initialValue = null): mixed
    {
        $accumulator = $initialValue;
        foreach ($this->iterator as $row) {
            $accumulator = $reducer($accumulator, $row);
        }
        return $accumulator;
    }

    /** @return Traversable  */
    public function get(): Traversable
    {
        return $this->iterator;
    }

    /**
     * @param int $limit
     * @return Pipeline
     * @throws Throwable
     */
    public function take(int $limit): self
    {
        $previousIterator = $this->iterator;

        $takeGenerator = function () use ($limit, $previousIterator): Generator {
            if ($limit <= 0) {
                return;
            }
            $count = 0;
            foreach ($previousIterator as $row) {
                yield $row;
                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
        };

        $this->iterator = $takeGenerator();
        return $this;
    }

    public function takeWhile(callable $condition): self
    {
        $previousIterator = $this->iterator;

        $takeWhileGenerator = function() use ($condition, $previousIterator): Generator {
            foreach ($previousIterator as $row) {
                // If condition is not met continue
                if ($condition($row)) {
                    yield $row;
                } else {
                    return;
                }
            }
        };

        $this->iterator = $takeWhileGenerator();

        return $this;
    }

    /**
     * @param LoaderInterface $loader
     * @return Stats An object containing the statistics of the run.
     */
    public function load(LoaderInterface $loader): Stats
    {
        $stats = new Stats();
        $stats->start();

        $generatorWithStats = function() use ($stats) {
            foreach($this->iterator as $row) {
                $stats->incrementRowsProcessed();
                yield $row;
            }
        };

        $loader->load($generatorWithStats());

        $stats->stop();
        return $stats;
    }

    /**
     * The core helper method for chaining transformations with error handling.
     *
     * @param callable $operation The specific logic for the transformation.
     * @return self
     */
    private function pipe(callable $operation): self
    {
        $previousIterator = $this->iterator;
        $generator = function () use ($operation, $previousIterator): Generator {
            foreach ($previousIterator as $row) {
                try {
                    $result = $operation($row);
                    if ($result !== self::getSkip()) {
                        yield $result;
                    }
                } catch (Throwable $e) {
                    if ($this->errorHandler) {
                        ($this->errorHandler)($e, $row);
                    } else {
                        // This prevents silent failures.
                        throw $e;
                    }
                }
            }
        };
        $this->iterator = $generator();
        return $this;
    }

    public static function getSkip()
    {
        if (self::$SKIP === null) {
            self::$SKIP = new stdClass();
        }
        return self::$SKIP;
    }
}
