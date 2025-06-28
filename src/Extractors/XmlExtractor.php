<?php

namespace Torol\Extractors;

use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Traversable;

class XmlExtractor implements ExtractorInterface
{
    /**
     * @param string $sourceFile The path to the file XML to extract data from.
     * @param string $xPathExpression The path to the file XML to extract data from.
     */
    public function __construct(
        private string $sourceFile,
        private string $xPathExpression,
    ) {
    }

    public function extract(): Traversable
    {
        if (! file_exists($this->sourceFile)) {
            throw new ExtractionException("File not found at path: {$this->sourceFile}");
        }

        $xml = simplexml_load_file($this->sourceFile);

        if ($xml === false) {
             throw new ExtractionException("Could not open file: {$this->sourceFile}");
        }

        $nodes = $xml->xpath($this->xPathExpression);

        foreach ($nodes as $node) {
            yield json_decode(json_encode($node), true);
        }
    }
}
