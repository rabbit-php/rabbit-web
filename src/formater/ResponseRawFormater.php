<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 14:18
 */

namespace rabbit\web\formater;


use Psr\Http\Message\ResponseInterface;
use rabbit\server\AttributeEnum;

class ResponseRawFormater implements ResponseFormaterInterface
{
    public function format(ResponseInterface $response): ResponseInterface
    {
        $data = $response->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'text/plain');
        $response = $response->withCharset($response->getCharset() ?? "UTF-8");
        // Content
        $data && $response = $response->withContent($data);

        return $response;
    }

}