<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RequestContext
 * @package Rabbit\Web
 */
class RequestContext
{
    /**
     * @param ServerRequestInterface $request
     */
    public static function set(ServerRequestInterface $request)
    {
        getContext()['request'] = $request;
    }

    /**
     * @return ServerRequestInterface|null
     */
    public static function get(): ?ServerRequestInterface
    {
        $context = getContext();
        if ($context && isset($context['request'])) {
            return $context['request'];
        }
        return null;
    }
}
