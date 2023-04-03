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
     * @Flow\InjectConfiguration(path="allowOrigins")
     */
    protected $allowOrigins;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="allowMethods")
     */
    protected $allowMethods;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="allowHeaders")
     */
    protected $allowHeaders;

    /**
     * @var string
     * @Flow\InjectConfiguration(path="exposeHeaders")
     */
    protected $exposeHeaders;

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
            is_string($this->allowOrigins) ? Arrays::trimExplode(',', $this->allowOrigins) : $this->allowOrigins,
            is_string($this->allowMethods) ? Arrays::trimExplode(',', $this->allowMethods) : $this->allowMethods,
            is_string($this->allowHeaders) ? Arrays::trimExplode(',', $this->allowHeaders) : $this->allowHeaders,
            is_string($this->exposeHeaders) ? Arrays::trimExplode(',', $this->exposeHeaders) : $this->exposeHeaders,
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
