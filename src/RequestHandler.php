<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Class RequestHandler
 * @package Rabbit\Web
 */
class RequestHandler implements RequestHandlerInterface
{
    private ?array $middlewares = null;
    private int $offset = 0;

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares[$this->offset])) {
            return ResponseContext::get();
        } else {
            $handler = $this->middlewares[$this->offset];
        }
        \is_string($handler) && $handler = getDI($handler);

        if (!$handler instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException('Invalid Handler. It must be an instance of MiddlewareInterface');
        }
        $this->offset++;
        return $handler->process($request, $this);
    }
}
