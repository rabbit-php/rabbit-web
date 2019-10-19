<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 19:30
 */

namespace rabbit\web;

use rabbit\core\UserException;
use rabbit\httpserver\Response;

/**
 * Class HttpException
 * @package rabbit\web
 */
class HttpException extends UserException
{
    /**
     * @var int HTTP status code, such as 403, 404, 500, etc.
     */
    public $statusCode;


    /**
     * HttpException constructor.
     * @param $status
     * @param null $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($status, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if (isset(Response::$phrases[$this->statusCode])) {
            return Response::$phrases[$this->statusCode];
        }

        return 'Error';
    }
}
