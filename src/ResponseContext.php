<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Co;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseContext
 * @package Rabbit\Web
 */
class ResponseContext
{
    /**
     * @param ResponseInterface $request
     */
    public static function set(ResponseInterface $request)
    {
        Co::getContext(getRootId())['response'] = $request;
    }

    /**
     * @return ResponseInterface|null
     */
    public static function get(): ?ResponseInterface
    {
        $context = Co::getContext(getRootId());
        if ($context && isset($context['response'])) {
            return $context['response'];
        }
        return null;
    }

    /**
     * @return bool
     */
    public static function has(): bool
    {
        if ($context = Co::getContext(getRootId()) && isset($context['response'])) {
            return true;
        }
        return false;
    }
}