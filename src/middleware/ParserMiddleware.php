<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/15
 * Time: 16:27
 */

namespace rabbit\web\middleware;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use rabbit\core\ObjectFactory;
use rabbit\web\parser\RequestParser;
use rabbit\web\parser\RequestParserInterface;

/**
 * Class ParserMiddleware
 * @package rabbit\web\middleware
 */
class ParserMiddleware implements MiddlewareInterface
{
    /**
     * @var RequestParserInterface|string
     */
    private $parser = RequestParser::class;

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (is_string($this->parser)) {
            $this->parser = ObjectFactory::get($this->parser);
        }
        $request = $this->parser->parse($request);
        return $handler->handle($request);
    }

}