<?php

namespace rabbit\web\parser;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestParserInterface
 * @package rabbit\web\parser
 */
interface RequestParserInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ServerRequestInterface
     */
    public function parse(ServerRequestInterface $request): ServerRequestInterface;
}
