# Torol: A Simple and Elegant ETL Library for PHP

Torol is a modern, memory-efficient, and highly readable ETL (Extract, Transform, Load) library for PHP. Inspired by the simplicity of Laravel Collections, Torol provides a fluent, chainable API to build powerful data processing pipelines with minimal effort.

It's designed to handle large datasets with ease by processing data row-by-row using PHP Generators, ensuring a low memory footprint regardless of the data size.

## Why Choose Torol?

- **Fluent & Readable API**: Chain methods together to create clean and self-documenting data pipelines.
- **Memory Efficient**: Built with generators to process massive files (CSV, JSON, XML) without running out of memory.
- **Easily Extensible**: Add your own custom data sources (Extractors) and destinations (Loaders) with a simple interface.
- **Rich Transformations**: A comprehensive set of transformation methods to cover the most common data manipulation tasks.
- **Modern & Tested**: Built for modern PHP (^8.1+) and comes with a thorough test suite.

## Installation

You can install Torol via Composer.

```bash
composer require torol/torol
```

### Optional Dependencies

Torol is modular. To use certain extractors, you may need to install additional packages.

```bash
# To use the ExcelExtractor
composer require phpoffice/phpspreadsheet

# To use the S3Extractor
composer require aws/aws-sdk-php

# For making API requests with ApiExtractor
composer require guzzlehttp/guzzle
```

## Quick Start

Here's a simple example of how to read user data from a CSV file, add a new column, filter for active users, and then load the results into a new JSON file.

Assume you have a `users.csv` file:

```csv
id,name,email,status
1,John Doe,john@example.com,active
2,Jane Smith,jane@example.com,inactive
3,Peter Jones,peter@example.com,active
```

Now, let's process it with Torol:

```php
<?php

require 'vendor/autoload.php';

use Torol\Extractors\CsvExtractor;
use Torol\Loaders\JsonLoader;
use Torol\Pipeline;

$pipeline = new Pipeline(
    new CsvExtractor('path/to/users.csv')
);

$stats = $pipeline
    ->map(function (array $row) {
        // Convert status to uppercase
        $row['status'] = strtoupper($row['status']);
        return $row;
    })
    ->filter(function (array $row) {
        // Keep only active users
        return $row['status'] === 'ACTIVE';
    })
    ->addColumn('processed_at', fn() => date('Y-m-d H:i:s'))
    ->load(
        new JsonLoader('path/to/active_users.json')
    );

print_r($stats->toArray());
```

This will create an `active_users.json` file with the following content:

```json
[
    {
        "id": "1",
        "name": "John Doe",
        "email": "john@example.com",
        "status": "ACTIVE",
        "processed_at": "2024-10-26 10:30:00"
    },
    {
        "id": "3",
        "name": "Peter Jones",
        "email": "peter@example.com",
        "status": "ACTIVE",
        "processed_at": "2024-10-26 10:30:00"
    }
]
```

## Available Components

Torol comes with a variety of built-in components to get you started quickly.

| Component Type | Available Implementations |
|----------------|---------------------------|
| **Extractors** | `Array`, `Csv`, `Database`, `Json`, `Api`, `Excel`, `Xml`, `S3` |
| **Loaders** | `Callback`, `Csv`, `Database`, `Json` |
| **Transformers** | `map`, `filter`, `addColumn`, `removeColumn`, `renameColumn`, `select`, `merge`, `nest`, `sort`, `groupBy`, `unique`, `validate`, `dd`, and many more. |

## Full Documentation

For detailed information on every component and feature, please see the documentation in the `/docs` directory.

- **Detailed Extractor Docs**
- **Detailed Transformer Docs**
- **Detailed Loader Docs**

## License

Torol is open-source software licensed under the MIT license.
