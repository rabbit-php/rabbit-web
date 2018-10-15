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

interface IResponseFormatTool
{
    public function format(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;
}