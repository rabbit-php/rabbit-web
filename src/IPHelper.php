<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ServerRequestInterface;
use Rabbit\Base\Helper\ArrayHelper;

/**
 * Class IPHelper
 * @package Rabbit\Web
 */
class IPHelper
{
    public static function getClientIp(ServerRequestInterface $request): string
    {
        if ($ip = $request->getHeaderLine('X-REAL-IP')) {
            return $ip;
        } elseif ($ip = $request->getHeaderLine('X-FORWARDED-FOR')) {
            return $ip;
        } else {
            return ArrayHelper::getValue($request->getServerParams(), 'remote_addr', '127.0.0.1');
        }
    }
}
