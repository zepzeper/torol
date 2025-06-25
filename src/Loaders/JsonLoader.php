<?php

namespace Torol\Loaders;

use Torol\Contracts\LoaderInterface;
use Torol\Exceptions\TransformationException;
use Traversable;

class JsonLoader implements LoaderInterface
{
    public function __construct(
        private string $targetFile,
        private bool $prettyPrint = true
    ) {
    }

	public function load(Traversable $rows): int
    {
		$rowCount = 0;
        $data = [];

        foreach ($rows as $row) {
            $data[] = $row->toArray();
			$rowCount++;
        }

        $flags = JSON_UNESCAPED_SLASHES;
        if ($this->prettyPrint) {
            $flags |= JSON_PRETTY_PRINT;
        }

        $jsonString = json_encode($data, $flags);

        if ($jsonString === false) {
            throw new TransformationException('Failed to encode data to JSON. Error: ' . json_last_error_msg());
        }

        $directory = dirname($this->targetFile);
        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        file_put_contents($this->targetFile, $jsonString);

		return $rowCount;
    }
}
