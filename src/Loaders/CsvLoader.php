<?php

namespace Torol\Loaders;

use Torol\Contracts\LoaderInterface;
use Torol\Exceptions\ExtractionException;
use Traversable;

class CsvLoader implements LoaderInterface
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

    public function load(Traversable $rows): int
    {
		$rowCount = 0;

        if (($handle = fopen($this->sourceFile, "w")) === false) {
             throw new ExtractionException("Could not open file: {$this->sourceFile}");
        }

        $headersWritten = false;

        foreach ($rows as $key => $row) {
            if (! $headersWritten) {
                $headers = array_keys($row->toArray());
                fputcsv($handle, $headers, $this->separator, $this->enclosure, $this->escape);
                $headersWritten = true;
            }

            fputcsv($handle, $row->toArray(), $this->separator, $this->enclosure, $this->escape);
			$rowCount++;
        }

        fclose($handle);
		return $rowCount;
    }
}
