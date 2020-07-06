<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestHandlerInterface
 * @package rabbit\server
 */
interface RequestHandlerInterface
{
    public function __invoke(array $params = [], ServerRequestInterface $request = null);
}
