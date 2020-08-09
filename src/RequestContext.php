<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Co;
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
        Co::getContext()['request'] = $request;
    }

    /**
     * @return ServerRequestInterface|null
     */
    public static function get(): ?ServerRequestInterface
    {
        $context = Co::getContext();
        if ($context && isset($context['request'])) {
            return $context['request'];
        }
        return null;
    }

    /**
     * @return bool
     */
    public static function has(): bool
    {
        if ($context = Co::getContext() && isset($context['request'])) {
            return true;
        }
        return false;
    }
}