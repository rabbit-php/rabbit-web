<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Interface ErrorHandlerInterface
 * @package rabbit\handler
 */
interface ErrorHandlerInterface
{
    /**
     * @param Throwable $throw
     * @return mixed
     */
    public function handle(Throwable $throw): ResponseInterface;
}
