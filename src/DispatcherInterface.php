<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface DispatcherInterface
 * @package Rabbit\Web
 */
interface DispatcherInterface
{
    /**
     * @Author Albert 63851587@qq.com
     * @DateTime 2020-10-23
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): void;
}
