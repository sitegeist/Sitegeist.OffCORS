<?php
declare(strict_types=1);

namespace Sitegeist\OffCORS\Middleware;

use Neos\Utility\Arrays;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Neos\Flow\Annotations as Flow;
use Sitegeist\OffCORS\Domain\Cors;

class CorsMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     * @Flow\InjectConfiguration(path="allowedOrigins")
     */
    protected $allowedOrigins;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="allowedMethods")
     */
    protected $allowedMethods;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="allowedHeaders")
     */
    protected $allowedHeaders;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="exposedHeaders")
     */
    protected $exposedHeaders;

    /**
     * @var bool
     * @Flow\InjectConfiguration(path="allowCredentials")
     */
    protected $allowCredentials;

    /**
     * @var int
     * @Flow\InjectConfiguration(path="maxAge")
     */
    protected $maxAge;

    /**
     * @Flow\Inject
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $cors = new Cors(
            $request,
            (bool) $this->allowCredentials,
            is_string($this->allowedHeaders) ? Arrays::trimExplode(',', $this->allowedHeaders) : $this->allowedHeaders,
            is_string($this->exposedHeaders) ? Arrays::trimExplode(',', $this->exposedHeaders) : $this->exposedHeaders,
            is_string($this->allowedOrigins) ? Arrays::trimExplode(',', $this->allowedOrigins) : $this->allowedOrigins,
            is_string($this->allowedMethods) ? Arrays::trimExplode(',', $this->allowedMethods) : $this->allowedMethods,
            (int) $this->maxAge
        );

        if ($cors->isPreflightRequest()) {
            $response = $this->responseFactory->createResponse(204);
            $response = $cors->addCorsHeaders($response);
            return $response;
        }

        $response = $handler->handle($request);

        if ($cors->isCorsRequest()) {
            $response = $cors->addCorsHeaders($response);
        }

        return $response;
    }
}
