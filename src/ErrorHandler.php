<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Throwable;
use Psr\Http\Message\ResponseInterface;
use Rabbit\Base\Helper\ExceptionHelper;
use Rabbit\HttpServer\Exceptions\HttpException;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param Throwable $throw
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(Throwable $throw, ResponseInterface $response): void
    {
        $message = ExceptionHelper::convertExceptionToArray($throw);
        if ($throw instanceof HttpException) {
            $response = $response->withStatus($throw->statusCode);
        } else {
            $response = $response->withStatus(500);
        }
        $body = $response->getBody();
        $body->seek(0);
        $body->write(json_encode($message, JSON_UNESCAPED_UNICODE));
    }
}
