<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 13:51
 */

namespace rabbit\web\formater;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface IResponseFormatTool
 * @package rabbit\web\formater
 */
interface IResponseFormatTool
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}