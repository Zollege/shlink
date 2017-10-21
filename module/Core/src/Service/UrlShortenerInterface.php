<?php
declare(strict_types=1);

namespace Shlinkio\Shlink\Core\Service;

use Psr\Http\Message\UriInterface;
use Shlinkio\Shlink\Common\Exception\RuntimeException;
use Shlinkio\Shlink\Core\Exception\EntityDoesNotExistException;
use Shlinkio\Shlink\Core\Exception\InvalidShortCodeException;
use Shlinkio\Shlink\Core\Exception\InvalidUrlException;

interface UrlShortenerInterface
{
    /**
     * Creates and persists a unique shortcode generated for provided url
     *
     * @param UriInterface $url
     * @param string[] $tags
     * @return string
     * @throws InvalidUrlException
     * @throws RuntimeException
     */
    public function urlToShortCode(UriInterface $url, array $tags = []): string;

    /**
     * Tries to find the mapped URL for provided short code. Returns null if not found
     *
     * @param string $shortCode
     * @return string
     * @throws InvalidShortCodeException
     * @throws EntityDoesNotExistException
     */
    public function shortCodeToUrl(string $shortCode): string;
}
