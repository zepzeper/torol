## Extractors Documentation

Extractors are the starting point of any Torol pipeline. They are responsible for reading data from a source and yielding it row-by-row to the pipeline for processing.

### ArrayExtractor

Extracts data from a simple PHP array. Useful for testing or processing in-memory data.

**Constructor:** `new ArrayExtractor(array $data)`

| Parameter | Type    | Description              |
| :-------- | :------ | :----------------------- |
| `$data`   | `array` | The source array to process. |

**Example:**
```php
use Torol\Extractors\ArrayExtractor;

$data = [
    ['id' => 1, 'name' => 'Alice'],
    ['id' => 2, 'name' => 'Bob'],
];

$extractor = new ArrayExtractor($data);
```

### CsvExtractor

Extracts data from a CSV file. The first row is treated as the header by default.

**Constructor:** `new CsvExtractor(string $sourceFile, bool $firstRowAsHeader = true, string $separator = ',', string $enclosure = '"', string $escape = '\\')`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$sourceFile` | `string` | Path to the source CSV file. |
| `$firstRowAsHeader` | `bool` | Treat the first row as headers. |
| `$separator` | `string` | The field delimiter. |
| `$enclosure` | `string` | The field enclosure character. |
| `$escape` | `string` | The escape character. |

**Example:**
```php
use Torol\Extractors\CsvExtractor;

$extractor = new CsvExtractor('path/to/data.csv');
```

### JsonExtractor

Extracts objects from a JSON file. It can read from a specific pointer in the JSON structure.

**Constructor:** `new JsonExtractor(string $sourceFile, ?string $pointer = null)`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$sourceFile` | `string` | Path to the source JSON file. |
| `$pointer` | `?string` | A JSON Pointer to the array of objects to iterate. e.g., '/data/users'. |

**Example:**
```php
use Torol\Extractors\JsonExtractor;

// Reads from the root of the JSON file
$extractor = new JsonExtractor('path/to/data.json');

// Reads from the "users" array inside a "data" object
$extractorWithPointer = new JsonExtractor('path/to/data.json', '/data/users');
```

### DatabaseExtractor

Extracts data from a database table using a PDO connection.

**Constructor:** `new DatabaseExtractor(\PDO $pdo, string $table, array $columns = ['*'], ?string $whereClause = null, array $whereParams = [])`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$pdo` | `\PDO` | An active PDO connection instance. |
| `$table` | `string` | The name of the table to extract from. |
| `$columns` | `array` | An array of columns to select. |
| `$whereClause` | `?string` | An optional WHERE clause (e.g., status = ?). |
| `$whereParams` | `array` | Parameters to bind to the WHERE clause. |

**Example:**
```php
use Torol\Extractors\DatabaseExtractor;

$pdo = new \PDO('mysql:host=localhost;dbname=test', 'user', 'pass');
$extractor = new DatabaseExtractor($pdo, 'users', ['id', 'name'], 'is_active = ?', [1]);
```

### XmlExtractor

Extracts data from an XML file using an XPath expression to select nodes.

**Requires:** `ext-simplexml`

**Constructor:** `new XmlExtractor(string $sourceFile, string $xpathExpression)`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$sourceFile` | `string` | Path to the source XML file. |
| `$xpathExpression` | `string` | An XPath expression to select the nodes to iterate over. |

**Example:**
```php
use Torol\Extractors\XmlExtractor;

// Extracts all <product> elements from an XML file
$extractor = new XmlExtractor('path/to/products.xml', '/products/product');
```

### ExcelExtractor

Extracts data from an Excel file (.xlsx, .xls, etc.).

**Requires:** `composer require phpoffice/phpspreadsheet`

**Constructor:** `new ExcelExtractor(string $sourceFile, bool $firstRowAsHeader = true, string|int|null $sheet = null)`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$sourceFile` | `string` | Path to the Excel file. |
| `$firstRowAsHeader` | `bool` | Treat the first row as the header. |
| `$sheet` | `string\|int\|null` | Sheet name or index to read from. |

**Example:**
```php
use Torol\Extractors\ExcelExtractor;

// Reads from the active sheet of an Excel file
$extractor = new ExcelExtractor('path/to/spreadsheet.xlsx');

// Reads from a specific sheet named "Sales Data"
$extractorWithSheet = new ExcelExtractor('path/to/spreadsheet.xlsx', true, 'Sales Data');
```

### S3Extractor

Extracts the content of files from an Amazon S3 bucket "folder" (prefix).

**Requires:** `composer require aws/aws-sdk-php`

**Constructor:** `new S3Extractor(Aws\S3\S3Client $s3Client, string $bucket, string $prefix = '')`

| Parameter | Type | Description |
| :-------- | :--- | :---------- |
| `$s3Client` | `S3Client` | An authenticated S3 client instance. |
| `$bucket` | `string` | The name of the S3 bucket. |
| `$prefix` | `string` | The "folder" or prefix to read files from. |

**Example:**
```php
use Aws\S3\S3Client;
use Torol\Extractors\S3Extractor;

$s3Client = new S3Client(['version' => 'latest', 'region' => 'us-east-1']);
$extractor = new S3Extractor($s3Client, 'my-data-bucket', 'incoming/');
```
