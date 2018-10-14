<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/13
 * Time: 16:07
 */

namespace rabbit\web;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use rabbit\framework\core\Context;
use rabbit\framework\core\ObjectFactory;
use rabbit\framework\handler\RequestHandlerInterface;

class RouteHandler implements RequestHandlerInterface
{

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $route = explode('/', ltrim($request->getRequestTarget(), '/'));
        if (count($route) < 2) {
            throw new NotFoundHttpException("can not find the route:$route");
        }
        $controller = 'apis';
        foreach ($route as $index => $value) {
            if ($index === count($route) - 1) {
                $action = $value;
            } elseif ($index === count($route) - 2) {
                $controller .= '\controllers\\' . ucfirst($value) . 'Controller';
            } else {
                $controller .= '\\' . $value;
            }
        }
        $controller = ObjectFactory::get($controller);
        /**
         * @var ResponseInterface $response
         */
        $response = Context::get('response');
        return $response->withContent(call_user_func_array([$controller, $action], $request->getQueryParams()));
    }
}