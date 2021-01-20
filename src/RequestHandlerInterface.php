<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ServerRequestInterface;

interface RequestHandlerInterface
{
    public function __invoke(ServerRequestInterface $request);
}
