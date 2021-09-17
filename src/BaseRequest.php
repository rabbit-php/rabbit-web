<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Swoole\Http\Request;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class BaseRequest
 * @package rabbit\server
 */
class BaseRequest implements ServerRequestInterface
{
    use MessageTrait;

    private array $attributes = [];

    private array $cookieParams = [];

    private array $parsedBody;

    private array $bodyParams;

    private array $queryParams = [];

    private array $serverParams = [];

    private array $uploadedFiles = [];

    private string $method;

    private UriInterface $uri;

    private string $requestTarget;

    public function __construct(array $query = [], array $body = [], array $headers = [], array $server = [])
    {
        $this->withQueryParams($query)
            ->withParsedBody($body)
            ->withHeaders($headers);
        $this->uri = self::getUriFromGlobals($headers, $server);
    }

    public function withParsedBody($data)
    {
        $this->parsedBody = $data;
        return $this;
    }

    public function withQueryParams(array $query)
    {
        $this->queryParams = $query;
        return $this;
    }

    private function setHeaders(array $headers): BaseRequest
    {
        $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);
            $this->headers[$normalized] = $value;
        }
        return $this;
    }

    private static function getUriFromGlobals(array $headers = [], array $server = []): Uri
    {
        $uri = new Uri();
        $uri = $uri->withScheme(!empty($server['https']) && $server['https'] !== 'off' ? 'https' : 'http');

        $hasPort = false;
        if (isset($server['http_host'])) {
            $hostHeaderParts = explode(':', $server['http_host']);
            $uri = $uri->withHost($hostHeaderParts[0]);
            if (isset($hostHeaderParts[1])) {
                $hasPort = true;
                $uri = $uri->withPort($hostHeaderParts[1]);
            }
        } elseif (isset($server['server_name'])) {
            $uri = $uri->withHost($server['server_name']);
        } elseif (isset($server['server_addr'])) {
            $uri = $uri->withHost($server['server_addr']);
        } elseif (isset($header['host'])) {
            if (\strpos($header['host'], ':')) {
                $hasPort = true;
                list($host, $port) = explode(':', $header['host'], 2);

                if ($port !== '80') {
                    $uri = $uri->withPort($port);
                }
            } else {
                $host = $header['host'];
            }

            $uri = $uri->withHost($host);
        }

        if (!$hasPort && isset($server['server_port'])) {
            $uri = $uri->withPort($server['server_port']);
        }

        $hasQuery = false;
        if (isset($server['request_uri'])) {
            $requestUriParts = explode('?', $server['request_uri']);
            $uri = $uri->withPath($requestUriParts[0]);
            if (isset($requestUriParts[1])) {
                $hasQuery = true;
                $uri = $uri->withQuery($requestUriParts[1]);
            }
        }

        if (!$hasQuery && isset($server['query_string'])) {
            $uri = $uri->withQuery($server['query_string']);
        }

        return $uri;
    }

    public function withServerParams(array $serverParams): BaseRequest
    {
        $this->serverParams = $serverParams;
        return $this;
    }

    public function withCookieParams(array $cookies)
    {

        $this->cookieParams = $cookies;
        return $this;
    }

    public function getServerParams()
    {
        return $this->serverParams;
    }

    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    public function withUploadedFiles(array $uploadedFiles)
    {

        $this->uploadedFiles = $uploadedFiles;
        return $this;
    }

    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    public function withAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function withoutAttribute($name)
    {
        if (false === array_key_exists($name, $this->attributes)) {
            return $this;
        }


        unset($this->attributes[$name]);

        return $this;
    }

    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target == '') {
            $target = '/';
        }
        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    public function withRequestTarget($requestTarget)
    {
        if (preg_match('#\s#', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace');
        }

        $this->requestTarget = $requestTarget;
        return $this;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function withMethod($method)
    {
        $method = strtoupper($method);
        $methods = ['GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'HEAD'];
        if (!in_array($method, $methods)) {
            throw new \InvalidArgumentException('Invalid Method');
        }

        $this->method = $method;
        return $this;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $this->uri = $uri;

        if (!$preserveHost) {
            $this->updateHostFromUri();
        }

        return $this;
    }

    private function updateHostFromUri(): void
    {
        $host = $this->uri->getHost();

        if ($host === '') {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        if ($this->hasHeader('host')) {
            $header = $this->getHeaderLine('host');
        } else {
            $header = 'Host';
        }
        // Ensure Host is the first header.
        $this->headers = [$header => [$host]] + $this->headers;
    }
}
