<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 19:32
 */

namespace rabbit\web;

/**
 * Class BadRequestHttpException
 * @package rabbit\web
 */
class BadRequestHttpException extends HttpException
{
    /**
     * BadRequestHttpException constructor.
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}