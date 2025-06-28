<?php

namespace Torol\Extractors;

use Torol\Builder\ApiExtractorBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Torol\Contracts\ExtractorInterface;
use Torol\Exceptions\ExtractionException;
use Torol\Row;
use Traversable;

class ApiExtractor implements ExtractorInterface
{
    private ApiExtractorBuilder $builder;
    private ClientInterface $client;

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
     * Initializes the HTTP client. Prefers an injected client if available.
     * @return ClientInterface
     */
    private function initializeClient(): ClientInterface
    {
        // If a client was provided to the builder, use it.
        if ($injectedClient = $this->builder->getHttpClient()) {
            return $injectedClient;
        }

        $options = ['base_uri' => $this->builder->getBaseUri()];
        return new Client($options);
    }

    /**
     * @return Traversable<Row>
     * @throws ExtractionException
     */
    public function extract(): Traversable
    {
        $nextUrl = $this->builder->getEndpoint();
        $page = 1;
        $auth = $this->builder->getAuth();
        $stopCondition = $this->builder->getPaginationStopCondition();

        $oauth2Header = [];
        if ($auth && $auth['type'] === 'oauth2') {
            $accessToken = $this->getOAuth2Token($auth);
            $oauth2Header = ['Authorization' => 'Bearer ' . $accessToken];
        }

        while ($nextUrl) {
            $options = [];
            $queryParams = $this->builder->getQueryParams();

            $options['headers'] = array_merge($this->builder->getHeaders(), $oauth2Header);

            if ($auth) {
                switch ($auth['type']) {
                    case 'apiKey':
                        if ($auth['in'] === 'header') {
                            $options['headers'][$auth['key']] = $auth['token'];
                        } else {
                            $queryParams[$auth['key']] = $auth['token'];
                        }
                        break;
                    case 'basic':
                        $options['auth'] = [$auth['username'], $auth['password']];
                        break;
                }
            }

            // Handle Pagination
            $paginationConfig = $this->builder->getPagination();
            if ($paginationConfig && $paginationConfig['type'] === "offset") {
                $queryParams[$paginationConfig['pageParam']] = $page;
                $queryParams[$paginationConfig['limitParam']] = $paginationConfig['limit'];
            }
            $options['query'] = $queryParams;

            try {
                $response = $this->client->request('GET', $nextUrl, $options);
                $body = json_decode($response->getBody()->getContents(), true);

                $data = $this->getDataFromResponse($body);

                foreach ($data as $item) {
                    yield new Row($item);
                }

                if ($stopCondition === true) {
                    $nextUrl = null;
                } else {
                    $nextUrl = $this->getNextPageUrl($body, count($data));
                    $page++;
                }
            } catch (GuzzleException $e) {
                throw new ExtractionException("API request to '{$nextUrl}' failed: " . $e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    // The rest of the methods (getOAuth2Token, getDataFromResponse, getNextPageUrl) remain the same.

    /**
     * @param array $authConfig
     * @return string
     * @throws ExtractionException
     */
    private function getOAuth2Token(array $authConfig): string
    {
        try {
            // This client is temporary and only for getting the token.
            $tokenClient = new Client();
            $response = $tokenClient->post($authConfig['token_url'], [
                RequestOptions::FORM_PARAMS => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $authConfig['client_id'],
                    'client_secret' => $authConfig['client_secret'],
                    'scope' => implode(' ', $authConfig['scopes']),
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['access_token'])) {
                throw new ExtractionException("OAuth2 response did not include an access_token");
            }

            return $data['access_token'];
        } catch (GuzzleException $e) {
            throw new ExtractionException("Failed to retrieve OAuth2 token: " . $e->getMessage(), 0, $e);
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
            return is_array($body) ? $body : [];
        }

        $keys = explode('.', $dataKey);

        $data = $body;
        foreach ($keys as $key) {
            if (!is_array($data) || !isset($data[$key])) {
                return []; // Unknown key or not an array
            }
            $data = $data[$key];
        }

        return is_array($data) ? $data : [];
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
