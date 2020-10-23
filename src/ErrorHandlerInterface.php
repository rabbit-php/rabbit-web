<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Throwable;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface ErrorHandlerInterface
 * @package rabbit\handler
 */
interface ErrorHandlerInterface
{
    /**
     * @Author Albert 63851587@qq.com
     * @DateTime 2020-10-23
     * @param \Throwable $throw
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(Throwable $throw, ResponseInterface $response): void;
}
