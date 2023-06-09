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
     * @param string[] $allowOrigins
     * @param string[] $allowMethods
     * @param string[] $allowHeaders
     * @param string[] $exposeHeaders
     * @param int $maxAge
     */
    public function __construct(
        public readonly RequestInterface $request,
        public readonly bool             $allowCredentials,
        public readonly array            $allowOrigins,
        public readonly array            $allowMethods,
        public readonly array            $allowHeaders,
        public readonly array            $exposeHeaders,
        public readonly int              $maxAge
    )
    {
    }

    public function addCorsHeaders(ResponseInterface $response): ResponseInterface
    {
        $response = $response->withAddedHeader("Access-Control-Allow-Origin", $this->getAllowOriginHeaderValue());
        $response = $response->withAddedHeader("Access-Control-Allow-Credentials", $this->allowCredentials ? 'true' : 'false');
        $response = $response->withAddedHeader("Access-Control-Allow-Methods", $this->allowMethods ?: "");
        $response = $response->withAddedHeader("Access-Control-Allow-Headers", $this->allowHeaders ?: "");
        $response = $response->withAddedHeader("Access-Control-Expose-Headers", $this->exposeHeaders ?: "");
        $response = $response->withAddedHeader("Access-Control-Max-Age", $this->maxAge);
        return $response;
    }

    public function getAllowOriginHeaderValue(): string
    {
        $requestOrigin = $this->request->getHeaderLine('Origin');
        $hasWildcard = in_array('*', $this->allowOrigins);

        if ($hasWildcard && !$requestOrigin) {
            return '*';
        }

        if (in_array($requestOrigin, $this->allowOrigins) || $hasWildcard) {
            return $requestOrigin;
        }

        return '';
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
