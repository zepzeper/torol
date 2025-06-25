<?php

namespace Torol\Extractors;

use PDO;
use Traversable;
use Generator;
use RuntimeException;
use Torol\Contracts\ExtractorInterface;
use Torol\Row;

/**
 * Extracts data from a database using a PDO connection.
 */
class DatabaseExtractor implements ExtractorInterface
{
    /**
     * @param PDO $pdo The configured PDO database connection instance.
     * @param string $query The SQL query to execute for data extraction.
     */
    public function __construct(
        private PDO $pdo,
        private string $query
    ) {
        // TODO Ensure PDO will throw exceptions on error.
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Executes the query and yields each row from the result set.
     *
     * @return Generator<Row>
     */
    public function extract(): Traversable
    {
        try {
            $statement = $this->pdo->prepare($this->query);
            $statement->execute();

            // Fetch rows one by one in a memory-efficient way.
            // PDO::FETCH_ASSOC ensures we get associative arrays.
            while ($record = $statement->fetch(PDO::FETCH_ASSOC)) {
                yield new Row($record);
            }
        } catch (\PDOException $e) {
            throw new RuntimeException("Database extraction failed: " . $e->getMessage(), 0, $e);
        }
    }
}
