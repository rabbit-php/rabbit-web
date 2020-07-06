<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ResponseInterface;
use Rabbit\Base\Core\Context;
use Rabbit\Base\Helper\ExceptionHelper;
use Rabbit\Web\HttpException;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param Throwable $throw
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handle(Throwable $throw): ResponseInterface
    {
        /* @var ResponseInterface $response */
        $response = Context::get('response');
        if ($response === null) {
            throw $throw;
        }
        $message = ExceptionHelper::convertExceptionToArray($throw);
        if ($throw instanceof HttpException) {
            $response = $response->withStatus($throw->statusCode);
        } else {
            $response = $response->withStatus(500);
        }
        $response = $response->withContent(json_encode($message, JSON_UNESCAPED_UNICODE));

        return $response;
    }
}
