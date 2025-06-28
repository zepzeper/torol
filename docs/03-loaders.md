# Loaders Documentation

Loaders are the final step in a Torol pipeline. They take the processed data and load it into a destination, such as a file or a database. The `load()` method on the pipeline returns a `Stats` object with information about the process.

---

## `load(LoaderInterface $loader)`
This is the terminal method on the `Pipeline` class that executes the entire ETL process.

**Example:**
```php
use Torol\Loaders\CsvLoader;

$stats = $pipeline->load(new CsvLoader('path/to/output.csv'));

echo "Processed {$stats->totalRows} rows in {$stats->elapsedTimeInSeconds()} seconds.";
```

## CsvLoader

Loads data into a new CSV file.

**Constructor:** `new CsvLoader(string $destinationFile, bool $includeHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\')`

| Parameter | Type | Description |
|-----------|------|-------------|
| `$destinationFile` | `string` | Path to the destination CSV file. |
| `$includeHeader` | `bool` | Write the column names as the first line. |
| `$separator` | `string` | The field delimiter. |
| `$enclosure` | `string` | The field enclosure character. |
| `$escape` | `string` | The escape character. |

**Example:**
```php
use Torol\Loaders\CsvLoader;

$loader = new CsvLoader('path/to/processed_data.csv');
```

## JsonLoader

Loads data into a JSON file.

**Constructor:** `new JsonLoader(string $destinationFile, int $flags = JSON_PRETTY_PRINT)`

| Parameter | Type | Description |
|-----------|------|-------------|
| `$destinationFile` | `string` | Path to the destination JSON file. |
| `$flags` | `int` | Bitmask of `json_encode` options. |

**Example:**
```php
use Torol\Loaders\JsonLoader;

// Creates a nicely formatted JSON file
$loader = new JsonLoader('path/to/output.json');

// Creates a compact JSON file
$loaderCompact = new JsonLoader('path/to/output.min.json', 0);
```

## DatabaseLoader

Loads data into a database table using a PDO connection. It inserts data in batches for efficiency.

**Constructor:** `new DatabaseLoader(\PDO $pdo, string $table, int $batchSize = 100)`

| Parameter | Type | Description |
|-----------|------|-------------|
| `$pdo` | `\PDO` | An active PDO connection instance. |
| `$table` | `string` | The name of the table to load data into. |
| `$batchSize` | `int` | The number of rows to insert in each batch. |

**Example:**
```php
use Torol\Loaders\DatabaseLoader;

$pdo = new \PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$loader = new DatabaseLoader($pdo, 'users_archive', 500);
```

## CallbackLoader

A flexible loader that processes each row with a custom callback function. Useful for sending data to an API, a logging service, or any other custom destination.

**Constructor:** `new CallbackLoader(Closure $callback)`

| Parameter | Type | Description |
|-----------|------|-------------|
| `$callback` | `Closure` | A function that receives each processed row. |

**Example:**
```php
use Torol\Loaders\CallbackLoader;

// Example: Send each user to an external API
$loader = new CallbackLoader(function(array $row) {
    // Your custom logic here, e.g.:
    // $httpClient->post('https://api.example.com/users', ['json' => $row]);
    echo "Processing user {$row['id']}\n";
});
```
