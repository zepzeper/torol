<?php

namespace Torol\Extractors;

use Aws\S3\S3Client;
use RuntimeException;
use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Traversable;

class S3Extractor implements ExtractorInterface
{
    /**
     * @param Aws\S3\S3Client $s3Client the authenticated client
     * @param string $bucket name of the bucket
     * @param string $prefix folder to read from
     * @throws RuntimeException
     */
    public function __construct(
        private S3Client $s3Client,
        private string $bucket,
        private string $prefix = ''
    )
    {
        if (!class_exists(S3Client::class)) {
            throw new RuntimeException('The aws/aws-sdk-php package is required to use the S3Extractor. Please run "composer require aws/aws-sdk-php".');
        }
    }

    public function extract(): Traversable
    {
        $paginator = $this->s3Client->getPaginator('ListObjectV2', [
            'Bucket' => $this->bucket,
            'Prefix' => $this->prefix,
        ]);

        foreach ($paginator as $result) {
            foreach ($result['Results'] ?? [] as $object) {
                // Skip ending with /
                if (substr($object['Key'], -1) === '/') {
                    continue;
                }

                try {
                    $file = $this->s3Client->getObject([
                        'Bucket' => $this->bucket,
                        'Key' => $object['Key']
                    ]);

                    yield [
                        'key' => $object['key'],
                        'content' => (string) $file['body'],
                        'metadata' => $file['@metadata'],
                    ];

                } catch (ExtractionException $e) {
                    throw new ExtractionException("Failed to get object '{$object['Key']}' from bucket '{$this->bucket}': " . $e->getMessage());
                }
            }
        }

    }
}
