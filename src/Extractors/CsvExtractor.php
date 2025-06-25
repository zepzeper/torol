<?php

namespace Torol\Extractors;

use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Torol\Row;
use Traversable;

class CsvExtractor implements ExtractorInterface
{
    /**
     * @param string $sourceFile The path to the CSV file to extract data from.
     * @param string $separator The field delimiter (one single-byte character).
     * @param string $enclosure The field enclosure character (one single-byte character).
     * @param string $escape The escape character (one single-byte character).
     */
    public function __construct(
        private string $sourceFile,
        private string $separator = ',',
        private string $enclosure = '"',
        private string $escape = '\\'
    ) {
    }

    /**
     * Opens the CSV, reads it line by line, and yields a new Row object for each record.
     *
     * @return Generator<Row>
     */
    public function extract(): Traversable
    {

        if (!file_exists($this->sourceFile)) {
            throw new ExtractionException("File not found at path: {$this->sourceFile}");
        }
        if (($handle = fopen($this->sourceFile, "r")) === false) {
             throw new ExtractionException("Could not open file: {$this->sourceFile}");
        }

        if (($headers = fgetcsv($handle, 0, $this->separator, $this->enclosure, $this->escape)) === false) {
            fclose($handle);
            return;
        }

        while (($data = fgetcsv($handle, 0, $this->separator, $this->enclosure, $this->escape)) !== false) {
            if (count($headers) === count($data)) {
                yield new Row(array_combine($headers, $data));
            }
        }

        fclose($handle);
    }
}
