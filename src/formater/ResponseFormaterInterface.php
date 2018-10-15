<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:56
 */

namespace rabbit\web\formater;


use Psr\Http\Message\ResponseInterface;

interface ResponseFormaterInterface
{
    public function format(ResponseInterface $response): ResponseInterface;
}