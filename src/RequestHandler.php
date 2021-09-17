<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Rabbit\Base\Core\BaseObject;
use function is_string;

/**
 * Class RequestHandler
 * @package Rabbit\Web
 */
class RequestHandler extends BaseObject implements RequestHandlerInterface
{
    protected ?array $middlewares = null;
    private int $offset = 0;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (empty($this->middlewares[$this->offset])) {
            return ResponseContext::get();
        } else {
            $handler = $this->middlewares[$this->offset];
        }
        is_string($handler) && $handler = getDI($handler);

        if (!$handler instanceof MiddlewareInterface) {
            throw new \InvalidArgumentException('Invalid Handler. It must be an instance of MiddlewareInterface');
        }
        $this->offset++;
        return $handler->process($request, $this);
    }
}
