<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Rabbit\Log\Logger;
use DI\NotFoundException;
use DI\DependencyException;
use Rabbit\Log\ConsoleColor;
use Rabbit\Base\Core\Context;
use Rabbit\Log\TemplateInterface;

/**
 * Class RegisterTemplateHandler
 * @package Rabbit\Web
 */
class RegisterTemplateHandler implements TemplateInterface
{
    /** @var array */
    protected array $possibleStyles = [];

    /**
     * RegisterTemplateHandler constructor.
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function __construct()
    {
        $this->possibleStyles = (array)(create(ConsoleColor::class)->getPossibleStyles());
    }

    /**
     * @return array
     */
    public function handle(): array
    {
        if (($request = Context::get(Logger::CONTEXT_KEY)) === null) {
            if ($serverRequest = RequestContext::get()) {
                $uri = $serverRequest->getUri();
                $requestId = $serverRequest->getAttribute(AttributeEnum::REQUESTID_ATTRIBUTE);
                !$requestId && $requestId = uniqid();
                $request = array_filter([
                    '%Q' => $requestId,
                    '%R' => $uri->getPath(),
                    '%m' => $serverRequest->getMethod(),
                    '%I' => IPHelper::getClientIp($serverRequest),
                    '%c' => [
                        $this->possibleStyles[rand(0, count($this->possibleStyles) - 1)]
                    ]
                ]);
            } else {
                $request = array_filter([
                    '%Q' => uniqid(),
                    '%c' => [
                        $this->possibleStyles[rand(0, count($this->possibleStyles) - 1)]
                    ]
                ]);
            }
            Context::set(Logger::CONTEXT_KEY, $request);
        }
        return $request;
    }
}
