<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 * @package Rabbit\Web
 */
class Uri implements UriInterface
{
    const DEFAULT_HTTP_HOST = 'localhost';

    private static array $defaultPorts = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    private static string $charUnreserved = 'a-zA-Z0-9_\-\.~';

    private static string $charSubDelims = '!\$&\'\(\)\*\+,;=';

    private static array $replaceQuery = ['=' => '%3D', '&' => '%26'];

    private string $scheme = '';

    private string $userInfo = '';

    private string $host = '';

    private ?int $port = null;

    private string $path = '';

    private string $query = '';

    private string $fragment = '';

    public function __construct(string $uri = '')
    {
        // weak type check to also accept null until we can add scalar type hints
        if ($uri != '') {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new \InvalidArgumentException("Unable to parse URI: $uri");
            }

            $this->applyParts($parts);
        }
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getAuthority()
    {
        $authority = $this->host;
        if ($this->userInfo !== '') {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }
        return $authority;
    }

    public function getUserInfo()
    {
        return $this->userInfo;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }

    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }

        $this->scheme = $scheme;
        $this->removeDefaultPort();
        $this->validateState();
        return $this;
    }

    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password !== '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }

        $this->userInfo = $user;
        $this->validateState();
    }

    public function withHost($host)
    {
        $host = $this->filterHost($host);
        if ($this->host === $host) {
            return $this;
        }

        $this->host = $host;
        $this->validateState();
        return $this;
    }

    public function withPort($port)
    {
        $port = $this->filterPort($port ?? (int)$port);
        if ($this->port === $port) {
            return $this;
        }

        $this->port = $port;
        $this->validateState();
        return $this;
    }

    public function withPath($path)
    {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }

        $this->path = $path;
        $this->validateState();
        return $this;
    }

    public function withQuery($query)
    {
        $query = $this->filterQueryAndFragment($query);
        if ($this->query === $query) {
            return $this;
        }

        $this->query = $query;
        return $this;
    }

    public static function withQueryValue(UriInterface $uri, string $key, ?string $value)
    {
        $current = $uri->getQuery();

        if ($current === '') {
            $result = [];
        } else {
            $decodedKey = rawurldecode($key);
            $result = array_filter(explode('&', $current), function (string $part) use ($decodedKey): bool {
                return rawurldecode(explode('=', $part)[0]) !== $decodedKey;
            });
        }

        // Query string separators ("=", "&") within the key or value need to be encoded
        // (while preventing double-encoding) before setting the query string. All other
        // chars that need percent-encoding will be encoded by withQuery().
        $key = strtr($key, self::$replaceQuery);

        if ($value !== null) {
            $result[] = $key . '=' . strtr($value, self::$replaceQuery);
        } else {
            $result[] = $key;
        }

        return $uri->withQuery(implode('&', $result));
    }

    public function withFragment($fragment)
    {
        $fragment = $this->filterQueryAndFragment($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }

        $this->fragment = $fragment;
        return $this;
    }

    public function __toString()
    {
        return self::composeComponents(
            $this->scheme,
            $this->getAuthority(),
            $this->path,
            $this->query,
            $this->fragment
        );
    }

    public static function composeComponents(string $scheme, string $authority, string $path, string $query, string $fragment): string
    {
        $uri = '';
        // weak type checks to also accept null until we can add scalar type hints
        if ($scheme != '') {
            $uri .= $scheme . ':';
        }
        if ($authority != '' || $scheme === 'file') {
            $uri .= '//' . $authority;
        }
        $uri .= $path;
        if ($query != '') {
            $uri .= '?' . $query;
        }
        if ($fragment != '') {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    private function validateState(): void
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::DEFAULT_HTTP_HOST;
        }
        if ($this->getAuthority() === '') {
            if (0 === strpos($this->path, '//')) {
                throw new \InvalidArgumentException('The path of a URI without an authority must not start with two slashes "//"');
            }
            if ($this->scheme === '' && false !== strpos(explode('/', $this->path, 2)[0], ':')) {
                throw new \InvalidArgumentException('A relative URI must not have a path beginning with a segment containing a colon');
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            $this->path = '/' . $this->path;
        }
    }

    private function applyParts(array $parts): void
    {
        $this->scheme = isset($parts['scheme']) ? $this->filterScheme($parts['scheme']) : '';
        $this->userInfo = isset($parts['user']) ? $parts['user'] : '';
        $this->host = isset($parts['host']) ? $this->filterHost($parts['host']) : '';
        $this->port = isset($parts['port']) ? $this->filterPort((int)$parts['port']) : null;
        $this->path = isset($parts['path']) ? $this->filterPath($parts['path']) : '';
        $this->query = isset($parts['query']) ? $this->filterQueryAndFragment($parts['query']) : '';
        $this->fragment = isset($parts['fragment']) ? $this->filterQueryAndFragment($parts['fragment']) : '';
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }

        $this->removeDefaultPort();
    }

    private function filterScheme(string $scheme): string
    {
        return strtolower($scheme);
    }

    private function filterHost(string $host): string
    {
        return strtolower($host);
    }

    private function filterPort(?int $port): ?int
    {
        if ($port === null) {
            return null;
        }

        if (1 > $port || 0xffff < $port) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. Must be between 1 and 65535', $port));
        }

        return $port;
    }

    private function removeDefaultPort()
    {
        if ($this->port !== null && $this->isDefaultPort()) {
            $this->port = null;
        }
    }

    public function isDefaultPort(): bool
    {
        return $this->getPort() === null || (isset(self::$defaultPorts[$this->getScheme()]) && $this->getPort() === self::$defaultPorts[$this->getScheme()]);
    }

    /**
     * @return int|null
     */
    public function getDefaultPort(): ?int
    {
        return self::$defaultPorts[$this->getScheme()] ?? null;
    }

    private function filterPath(string $path): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            [
                $this,
                'rawurlencodeMatchZero'
            ],
            $path
        );
    }

    private function filterQueryAndFragment(string $str): string
    {
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelims . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            [
                $this,
                'rawurlencodeMatchZero'
            ],
            $str
        );
    }

    private function rawurlencodeMatchZero(array $match): string
    {
        return rawurlencode($match[0]);
    }
}
