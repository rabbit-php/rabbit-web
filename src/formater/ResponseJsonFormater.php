<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 13:52
 */

namespace rabbit\web\formater;


use Psr\Http\Message\ResponseInterface;
use rabbit\helper\JsonHelper;
use rabbit\server\AttributeEnum;

/**
 * Class ResponseJsonFormater
 * @package rabbit\web\formater
 */
class ResponseJsonFormater implements ResponseFormaterInterface
{

    /**
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function format(ResponseInterface $response): ResponseInterface
    {
        //data
        $data = $response->getAttribute(AttributeEnum::RESPONSE_ATTRIBUTE);
        if ($data === null) {
            return $response;
        }
        // Headers
        $response = $response->withoutHeader('Content-Type')->withAddedHeader('Content-Type', 'application/json');
        $response = $response->withCharset($response->getCharset() ?? "UTF-8");

        // Content
        if ($data && ($response->isArrayable($data) || is_string($data))) {
            is_string($data) && $data = ['data' => $data];
            $content = JsonHelper::encode($data, JSON_UNESCAPED_UNICODE);
            $response = $response->withContent($content);
        } else {
            $response = $response->withContent('{}');
        }

        return $response;
    }
}