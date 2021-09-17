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
    public static function set(ResponseInterface $response)
    {
        getContext(getRootId())['response'] = $response;
    }

    public static function get(): ?ResponseInterface
    {
        $context = getContext(getRootId());
        if ($context && isset($context['response'])) {
            return $context['response'];
        }
        return null;
    }
}
