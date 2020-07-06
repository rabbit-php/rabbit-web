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
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}
