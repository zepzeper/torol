<?php

namespace Torol\Extractors;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;
use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Traversable;

class ExcelExtractor implements ExtractorInterface
{
    /**
     * @param string $sourceFile
     * @param bool $firstRowIsHeader
     * @param string|int|null $sheet
     */
    public function __construct(
        private string $sourceFile,
        private bool $firstRowIsHeader = true,
        private string|int|null $sheet = null
    )
    {
        if (!class_exists(IOFactory::class)) {
            throw new RuntimeException('The phpoffice/phpspreadsheet package is required to use the ExcelExtractor. Please run "composer require phpoffice/phpspreadsheet".');
        }
    }

    public function extract(): Traversable
    {
        if (! file_exists($this->sourceFile)) {
            throw new ExtractionException("File not found at path: {$this->sourceFile}");
        }

        $reader = IOFactory::createReaderForFile($this->sourceFile);
        $reader->setReadDataOnly(true);

        if ($this->sheet === null) {
            $reader->setLoadSheetsOnly(is_string($this->sheet) ? [$this->sheet] : $this->sheet);
        }

        $spreadSheet = $reader->load($this->sourceFile);

        $workSheet = ($this->sheet === null
              ? $spreadSheet->getActiveSheet()
              : (is_string($this->sheet)))
                 ? $spreadSheet->getSheetByName($this->sheet)
                 : $spreadSheet->getSheet($this->sheet);

        if (!$workSheet) {
            throw new ExtractionException("Sheet '{$this->sheet}' not found in {$this->sourceFile}.");
        }

        $rows = $workSheet->getRowIterator();
        $header = [];

        if ($this->firstRowIsHeader) {
            $firstRow = $rows->current();
            foreach ($firstRow->getCellIterator() as $cell) {
                $header[] = $cell->getValue();
            }
            $rows->next();
        }

        foreach ($rows as $row) {
            $rowData = $this->getRowData($row);

            if (empty($rowData)) {
                continue;
            }

            if ($this->firstRowIsHeader) {
                $paddedRow = array_pad($rowData, count($header), null);
                yield array_combine($header, $paddedRow);
            } else {
                yield $rowData;
            }
        }

    }

    /**
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Row $row
     * @return array
     */
    private function getRowData($row): array
    {
        $data = [];
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(true);

        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue();
        }

        return $data;
    }
}
