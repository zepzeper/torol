<?php

namespace Torol\Builder;

use LogicException;
use Torol\Extractors\ApiExtractor;

class ApiExtractorBuilder {

    private string $endpoint;
    private array $headers = [];
    private array $queryParams = [];
    private ?array $auth = null;
    private ?array $pagination = null;
    private ?string $dataKey = "data";

    public function __construct(
        private string $baseUri
    )
    {
    }

    /**
     * @param string $endpoint 
     * @return ApiExtractorBuilder 
     */
    public function endpoint(string $endpoint): self
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * @param string $key 
     * @param string $value 
     * @return ApiExtractorBuilder 
     */
    public function withHeader(string $key, string $value): self 
    {
        $this->headers[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, string> $headers 
     * @return ApiExtractorBuilder 
     */
    public function withHeaders(array $headers): self 
    {
        foreach ($headers as $key => $value) {
            $this->headers[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $key 
     * @param string $value 
     * @return ApiExtractorBuilder 
     */
    public function withQueryParam(string $key, string $value): self
    {
        $this->queryParams[$key] = $value;

        return $this;
    }

    /**
     * @param array<string, string> $headers 
     * @return ApiExtractorBuilder 
     */
    public function withQueryParams(array $params): self 
    {
        foreach ($params as $key => $value) {
            $this->queryParams[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $key 
     * @param string $token 
     * @param string $in 
     * @return ApiExtractorBuilder 
     */
    public function withApiKey(string $key, string $token, string $in = 'header'): self
    {
        $this->auth = [
            'type' => 'apiKey',
            'key' => $key,
            'token' => $token,
            'in' => $in,
        ];
        return $this;
    }

    /**
     * @param string $username 
     * @param string $password 
     * @return ApiExtractorBuilder 
     */
    public function withBasicAuth(string $username, string $password): self
    {
        $this->auth = [
            'type' => 'basic',
            'username' => $username,
            'password' => $password,
        ];
        return $this;
    }

    /**
     * @param string $tokenUrl 
     * @param string $clientId 
     * @param string $clientSecret 
     * @param array $scopes 
     * @return ApiExtractorBuilder 
     */
    public function withOAuth2ClientCredentials(string $tokenUrl, string $clientId, string $clientSecret, array $scopes = []): self
    {
        $this->auth = [
            'type' => 'oauth2',
            'grant_type' => 'client_credentials',
            'token_url' => $tokenUrl,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scopes' => $scopes,
        ];
        return $this;
    }

    /**
     * @param int $limit 
     * @param string $limitParam 
     * @param string $pageParam 
     * @return ApiExtractorBuilder 
     */
    public function withOffsetPagination(int $limit = 100, string $limitParam = 'limit', string $pageParam = 'page'): self
    {
        $this->pagination = [
            'type' => 'offset',
            'limit' => $limit,
            'limitParam' => $limitParam,
            'pageParam' => $pageParam,
        ];
        return $this;
    }

    public function withCursorPagination(string $nextPageKey = 'links.next'): self
    {
        $this->pagination = [
            'type' => 'cursor',
            'key' => $nextPageKey,
        ];
        return $this;
    }

    /**
     * @param null|string $key 
     * @return ApiExtractorBuilder 
     */
    public function withDataKey(?string $key): self
    {
        $this->dataKey = $key;
        return $this;
    }


    /**
     * @return ApiExtractor 
     * @throws LogicException 
     */
    public function build(): ApiExtractor
    {
        if (empty($this->endpoint)) {
            throw new \LogicException("Cannot build ApiExtractor without an endpoint.");
        }

        return ApiExtractor::from($this);
    }

    public function getBaseUri(): string { return $this->baseUri; }
    public function getEndpoint(): string { return $this->endpoint; }
    public function getHeaders(): array { return $this->headers; }
    public function getQueryParams(): array { return $this->queryParams; }
    public function getAuth(): ?array { return $this->auth; }
    public function getPagination(): ?array { return $this->pagination; }
    public function getDataKey(): ?string { return $this->dataKey; }
}
