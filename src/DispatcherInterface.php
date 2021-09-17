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
    public function dispatch(ServerRequestInterface $request): ResponseInterface;
}
