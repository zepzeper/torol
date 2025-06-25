<?php

namespace Torol\Loaders;

use PDO;
use PDOStatement;
use RuntimeException;
use Traversable;
use Torol\Contracts\LoaderInterface;
use Torol\Row;

class DatabaseLoader implements LoaderInterface
{
    private ?PDOStatement $statement = null;
    private array $batch = [];

    /**
     * @param PDO $pdo The configured PDO database connection instance.
     * @param string $tableName The name of the target database table.
     * @param int $batchSize The number of rows to insert in a single transaction.
     */
    public function __construct(
        private PDO $pdo,
        private string $tableName,
        private int $batchSize = 100
    ) {
    }

    public function load(Traversable $rows): int
    {
		$rowCount = 0;
        $this->pdo->beginTransaction();

        try {
            foreach ($rows as $row) {
                // If this is the first row, prepare the SQL statement.
                if ($this->statement === null) {
                    $this->prepareStatement($row);
                }

                $this->batch[] = $row->toArray();

                // If the batch is full, execute it.
                if (count($this->batch) >= $this->batchSize) {
                    $this->executeBatch();
                }
            }

            // Execute any remaining rows in the final batch.
            if (!empty($this->batch)) {
                $this->executeBatch();
            }

            $this->pdo->commit();
			$rowCount++;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw new RuntimeException("Database loading failed: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Creates a prepared SQL INSERT statement based on the columns of the first row.
     */
    private function prepareStatement(Row $firstRow): void
    {
        $columns = array_keys($firstRow->toArray());
        $columnList = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $query = "INSERT INTO {$this->tableName} ({$columnList}) VALUES ({$placeholders})";

        $this->statement = $this->pdo->prepare($query);
    }

    /**
     * Executes the prepared statement for all rows in the current batch.
     */
    private function executeBatch(): void
    {
        if ($this->statement === null) {
            return; // Nothing to execute
        }

        foreach ($this->batch as $item) {
            $this->statement->execute(array_values($item));
        }

        // Clear the batch for the next set of rows.
        $this->batch = [];
    }
}
