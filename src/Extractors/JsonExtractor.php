<?php

namespace Torol\Extractors;

use JsonMachine\JsonMachine;
use Traversable;
use Generator;
use RuntimeException;
use Torol\Contracts\ExtractorInterface;
use Torol\Row;

class JsonExtractor implements ExtractorInterface
{
    /**
     * @param string $sourceFile The path to the JSON file.
     * @param string|null $jsonPointer A JSON Pointer string.
     */
    public function __construct(
        private string $sourceFile,
        private ?string $jsonPointer = null
    ) {
    }

    /**
     * Streams items from the JSON file and yields each one as a Row object.
     *
     * @return Generator<Row>
     */
    public function extract(): Traversable
    {
        if (!class_exists(JsonMachine::class)) {
            throw new RuntimeException('The "halaxa/json-machine" package is required. Please run "composer require halaxa/json-machine".');
        }

        if (!file_exists($this->sourceFile)) {
            throw new RuntimeException("File not found at path: {$this->sourceFile}");
        }

        try {
            $items = JsonMachine::fromFile($this->sourceFile, $this->jsonPointer);

            foreach ($items as $item) {
                // Ensure the item is an associative array before creating a Row.
                if (is_array($item)) {
                    yield new Row($item);
                }
            }
        } catch (\Exception $e) {
            throw new RuntimeException("JSON extraction failed: " . $e->getMessage(), 0, $e);
        }
    }
}
