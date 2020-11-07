<?php

declare(strict_types=1);

namespace Shlinkio\Shlink\Rest\Middleware;

use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Mezzio\Router\RouteResult;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Shlinkio\Shlink\Rest\Exception\MissingAuthenticationException;
use Shlinkio\Shlink\Rest\Exception\VerifyAuthenticationException;
use Shlinkio\Shlink\Rest\Service\ApiKeyServiceInterface;

use function Functional\contains;

class AuthenticationMiddleware implements MiddlewareInterface, StatusCodeInterface, RequestMethodInterface
{
    public const API_KEY_HEADER = 'X-Api-Key';

    private ApiKeyServiceInterface $apiKeyService;
    private array $routesWhitelist;

    public function __construct(ApiKeyServiceInterface $apiKeyService, array $routesWhitelist)
    {
        $this->apiKeyService = $apiKeyService;
        $this->routesWhitelist = $routesWhitelist;
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        /** @var RouteResult|null $routeResult */
        $routeResult = $request->getAttribute(RouteResult::class);
        if (
            $routeResult === null
            || $routeResult->isFailure()
            || $request->getMethod() === self::METHOD_OPTIONS
            || contains($this->routesWhitelist, $routeResult->getMatchedRouteName())
        ) {
            return $handler->handle($request);
        }

        $apiKey = self::apiKeyFromRequest($request);
        if (empty($apiKey)) {
            throw MissingAuthenticationException::fromExpectedTypes([self::API_KEY_HEADER]);
        }

        if (! $this->apiKeyService->check($apiKey)) {
            throw VerifyAuthenticationException::forInvalidApiKey();
        }

        return $handler->handle($request);
    }

    public static function apiKeyFromRequest(Request $request): string
    {
        return $request->getHeaderLine(self::API_KEY_HEADER);
    }
}
