<?php
declare(strict_types=1);

namespace Rabbit\Web;
/**
 * Class NotFoundHttpException
 * @package Rabbit\Web
 */
class NotFoundHttpException extends HttpException
{
    /**
     * NotFoundHttpException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
