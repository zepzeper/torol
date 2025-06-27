<?php

namespace Torol\Extractors;

use Torol\Builder\ApiExtractorBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Torol\Row;
use Traversable;

class ApiExtractor implements ExtractorInterface {

    private ApiExtractorBuilder $builder;
    private Client $client;

    private function __construct(ApiExtractorBuilder $builder)
    {
        $this->builder = $builder;
        $this->client = $this->initializeClient();
    }


    /**
     * @param ApiExtractorBuilder $apiExtractorBuilder 
     * @return ApiExtractor 
     */
    public static function from(ApiExtractorBuilder $apiExtractorBuilder): self
    {
        return new static($apiExtractorBuilder);
    }

    /**
     * @return Client 
     * @throws ExtractionException 
     */
    private function initializeClient(): Client
    {
        $options = [
            'base_uri' => $this->builder->getBaseUri(),
            'headers' => $this->builder->getHeaders(),
        ];

        if ($auth = $this->builder->getAuth()) {
            switch ($auth) {
                case 'apiKey':
                    if ($auth['in'] === 'header') {
                        $options['headers'][$auth['key']] = $auth['token'];
                    } else {
                        // API key in query params will be added to every request in the extract loop
                    }
                case 'basic':
                    $options['auth'] = [$auth['username'], $auth['password']];
                    break;

                case 'oauth2':
                    $accessToken = $this->getOAuth2Token($auth);
                    $options['headers']['Authorization'] = 'Bearer ' . $accessToken;
                    break;
            }
        }

        return new Client($options);
    }

    /**
     * @param array $authConfig 
     * @return string 
     * @throws ExtractionException 
     */
    private function getOAuth2Token(array $authConfig): string
    {
        try {
            $tokenClient = new Client($authConfig);
            $response = $tokenClient->post($authConfig['url'], [
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $authConfig['client_id'],
                    'client_secret' => $authConfig['client_secret'],
                    'scope' => implode(' ', $authConfig['scopes']),
                ],
            ]);

            if (empty($response['access_token'])) {
               throw new ExtractionException("OAuth2 response did not include an access_token");
            }

            return $response['access_token'];

        } catch (GuzzleException $e) {
            throw new ExtractionException("Failed to retrieve OAuth2 token: " . $e->getMessage(), 0, $e);
        }

    }

    /**
     * @return Traversable<Row> 
     * @throws ExtractionException 
     */
    public function extract(): Traversable 
    {
        $nextUrl = $this->builder->getEndpoint();
        $page = 1;

        while ($nextUrl) {
            $queryParams = $this->builder->getQueryParams();
            $paginationConfig = $this->builder->getPagination();

            // Api key in query
            $auth = $this->builder->getAuth();
            if ($auth && $auth['type'] === "apiKey" && $auth['in'] === "query") {
                $queryParams[$auth['key']] = $auth['token'];
            }

            if ($paginationConfig && $paginationConfig['type'] === "offset") {
                $queryParams[$paginationConfig['pageParam']] = $page;
                $queryParams[$paginationConfig['limitParam']] = $paginationConfig['limit'];
            }

            try {
                $response = $this->client->get($nextUrl, ['query' => $queryParams]);
                $body = json_decode($response->getBody()->getContents(), true);

                $data = $this->getDataFromResponse($body);

                foreach ($data as $item) {
                    yield new Row($item);
                }

                $nextUrl = $this->getNextPageUrl($body, count($data));
                $page++;
                
            } catch (GuzzleException $e) {
                throw new ExtractionException("API request to '{$nextUrl}' failed: " . $e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /**
     * @param array $body 
     * @return array 
     */
    private function getDataFromResponse(array $body): array
    {
        $dataKey = $this->builder->getDataKey();

        if ($dataKey === null) {
            return $body;
        }

        $keys = explode('.', $dataKey);

        $data = $body;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return []; // Unkown key
            }

            $data = $data[$key];
        }

        return $data;
    }


    /**
     * @param array $body 
     * @param int $resultsCount 
     * @return null|string 
     */
    private function getNextPageUrl(array $body, int $resultsCount): ?string
    {
        $paginationConfig = $this->builder->getPagination();

        if (!$paginationConfig) {
            return null; // No pagination configured
        }

        switch ($paginationConfig['type']) {
            case 'offset':
                return $resultsCount < $paginationConfig['limit'] ? null : $this->builder->getEndpoint();
            case 'cursor':
                $key = $paginationConfig['key'];
                // Find the nested key for the next page URL
                $keys = explode('.', $key);
                $nextUrl = $body;
                foreach ($keys as $k) {
                    if (!isset($nextUrl[$k])) {
                        return null; // Next page key not found
                    }
                    $nextUrl = $nextUrl[$k];
                }
                return is_string($nextUrl) ? $nextUrl : null;
            default:
                return null;
        }
    }

}
