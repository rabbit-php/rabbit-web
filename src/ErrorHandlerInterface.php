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
    public function handle(Throwable $throw, ResponseInterface $response): void;
}
