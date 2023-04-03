<?php
declare(strict_types=1);

namespace Sitegeist\OffCORS\Domain;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Cors
{
    /**
     * @param RequestInterface $request
     * @param bool $allowCredentials
     * @param string[] $allowedHeaders
     * @param string[] $exposeHeaders
     * @param string[] $allowedOrigins
     * @param string[] $allowedMethods
     * @param int $maxAge
     */
    public function __construct(
        public readonly RequestInterface $request,
        public readonly bool $allowCredentials,
        public readonly array $allowedHeaders,
        public readonly array $exposeHeaders,
        public readonly array $allowedOrigins,
        public readonly array $allowedMethods,
        public readonly int $maxAge
    )
    {
    }

    public function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        $response = $response->withAddedHeader("Access-Control-Allow-Credentials", $this->getAllowCredentialsHeaderValue());
        $response = $response->withAddedHeader("Access-Control-Allow-Origin", $this->getAllowOriginHeaderValue());
        $response = $response->withAddedHeader("Access-Control-Allow-Methods", $this->getAllowMethodsHeaderValue() || "");
        $response = $response->withAddedHeader("Access-Control-Allow-Headers", $this->getAllowHeaders() || "");
        $response = $response->withAddedHeader("Access-Control-Expose-Headers", $this->getExposeHeaders() || "");
        $response = $response->withAddedHeader("Access-Control-Max-Age", $this->getMaxAge());
        return $response;
    }

    public function getAllowCredentialsHeaderValue(): string
    {
        return $this->allowCredentials ? 'true' : 'false';
    }

    public function getAllowOriginHeaderValue(): string
    {
        $requestOrigin = $this->request->getHeaderLine('Origin');
        $hasWildcard = in_array('*', $this->allowedOrigins);

        if ($hasWildcard && !$requestOrigin) {
            return '*';
        }

        if (in_array($requestOrigin, $this->allowedOrigins) || $hasWildcard) {
            return $requestOrigin;
        }

        return '';
    }

    public function getAllowMethodsHeaderValue(): array
    {
        return $this->allowedMethods;
    }

    public function getAllowHeaders(): array
    {
        return $this->allowedHeaders;
    }

    public function getExposeHeaders(): array
    {
        return $this->exposeHeaders;
    }
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    public function isCorsRequest(): bool
    {
        $origin = $this->request->getHeaderLine('Origin');
        $scheme = $this->request->getUri()->getScheme();
        $httpHost = $scheme.'://' . $this->request->getUri()->getHost();
        $port = $this->request->getUri()->getPort();

        if (('http' == $scheme && 80 == $port) || ('https' == $scheme && 443 == $port)) {
            $isSameHost = $origin === $httpHost;
        } else {
            $isSameHost = $origin === ($httpHost.':'.$port);
        }
        return $origin && !$isSameHost;
    }

    public function isPreflightRequest(): bool
    {
        return $this->isCorsRequest()
            && 'options' === strtolower( $this->request->getMethod())
            && $this->request->hasHeader('Access-Control-Request-Method');
    }
}
