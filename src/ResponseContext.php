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
    protected static ?ResponseInterface $response = null;
    /**
     * @param ResponseInterface $response
     */
    public static function set(ResponseInterface $response)
    {
        if (-1 !== $cid = getRootId()) {
            Co::getContext($cid)['response'] = $response;
        } else {
            self::$response = $response;
        }
    }

    /**
     * @return ResponseInterface|null
     */
    public static function get(): ?ResponseInterface
    {
        if (-1 !== $cid = getRootId()) {
            $context = Co::getContext($cid);
            if ($context && isset($context['response'])) {
                return $context['response'];
            }
            return null;
        }
        return self::$response === null ? null : (clone self::$response);
    }
}
