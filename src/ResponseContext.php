<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;

/**
 * Class ResponseContext
 * @package Rabbit\Web
 */
class ResponseContext
{
    /**
     * @param ResponseInterface $response
     */
    public static function set(ResponseInterface $response)
    {
        getContext(getRootId())['response'] = $response;
    }

    /**
     * @return ResponseInterface|null
     */
    public static function get(): ?ResponseInterface
    {
        $context = getContext(getRootId());
        if ($context && isset($context['response'])) {
            return $context['response'];
        }
        return null;
    }
}
