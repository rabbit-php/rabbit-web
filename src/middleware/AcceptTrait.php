<?php

namespace rabbit\web\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rabbit\core\ObjectFactory;
use rabbit\web\formater\IResponseFormatTool;
use rabbit\web\formater\ResponseFormater;

trait AcceptTrait
{
    /**
     * @var IResponseFormatTool
     */
    protected $formater = ResponseFormater::class;

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return \Psr\Http\Message\ResponseInterface|Response
     * @throws \InvalidArgumentException
     */
    protected function handleAccept(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        // Only handle HTTP-Server Response
        if (!$response instanceof ResponseInterface) {
            return $response;
        }
        if (is_string($this->formater)) {
            $this->formater = ObjectFactory::get($this->formater);
        }

        $response = $this->formater->format($request, $response);
        return $response;
    }

}
