<?php

declare(strict_types=1);

namespace Rabbit\Web;

/**
 * Class Cookie
 * @package Rabbit\Web
 */
class Cookie
{
    protected string $name;

    protected string $value;

    protected string $domain;

    protected int $expire;

    protected string $path;

    protected bool $secure;

    protected bool $httpOnly;

    private bool $raw;

    private string $sameSite;

    const SAMESITE_LAX = 'lax';
    const SAMESITE_STRICT = 'strict';

    public static function fromString(string $cookie, bool $decode = false)
    {
        $data = array(
            'expires' => 0,
            'path' => '/',
            'domain' => null,
            'secure' => false,
            'httponly' => false,
            'raw' => !$decode,
            'samesite' => null,
        );
        foreach (explode(';', $cookie) as $part) {
            if (false === strpos($part, '=')) {
                $key = trim($part);
                $value = true;
            } else {
                list($key, $value) = explode('=', trim($part), 2);
                $key = trim($key);
                $value = trim($value);
            }
            if (!isset($data['name'])) {
                $data['name'] = $decode ? urldecode($key) : $key;
                $data['value'] = true === $value ? null : ($decode ? urldecode($value) : $value);
                continue;
            }
            switch ($key = strtolower($key)) {
                case 'name':
                case 'value':
                    break;
                case 'max-age':
                    $data['expires'] = time() + (int)$value;
                    break;
                default:
                    $data[$key] = $value;
                    break;
            }
        }

        return new static(
            $data['name'],
            $data['value'],
            $data['expires'],
            $data['path'],
            $data['domain'],
            $data['secure'],
            $data['httponly'],
            $data['raw'],
            $data['samesite']
        );
    }

    public function __construct(
        string $name,
        string $value = null,
        int $expire = 0,
        string $path = '/',
        string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        bool $raw = false,
        string $sameSite = null
    ) {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = 0 < $expire ? (int)$expire : 0;
        $this->path = empty($path) ? '/' : $path;
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->raw = $raw;

        if (null !== $sameSite) {
            $sameSite = strtolower($sameSite);
        }

        if (!in_array($sameSite, array(self::SAMESITE_LAX, self::SAMESITE_STRICT, null), true)) {
            throw new \InvalidArgumentException('The "sameSite" parameter value is not valid.');
        }

        $this->sameSite = $sameSite;
    }

    public function __toString(): string
    {
        $str = ($this->isRaw() ? $this->getName() : urlencode($this->getName())) . '=';

        if ('' === (string)$this->getValue()) {
            $str .= 'deleted; expires=' . gmdate('D, d-M-Y H:i:s T', time() - 31536001) . '; max-age=-31536001';
        } else {
            $str .= $this->isRaw() ? $this->getValue() : rawurlencode($this->getValue());

            if (0 !== $this->getExpiresTime()) {
                $str .= '; expires=' . gmdate(
                    'D, d-M-Y H:i:s T',
                    $this->getExpiresTime()
                ) . '; max-age=' . $this->getMaxAge();
            }
        }

        if ($this->getPath()) {
            $str .= '; path=' . $this->getPath();
        }

        if ($this->getDomain()) {
            $str .= '; domain=' . $this->getDomain();
        }

        if (true === $this->isSecure()) {
            $str .= '; secure';
        }

        if (true === $this->isHttpOnly()) {
            $str .= '; httponly';
        }

        if (null !== $this->getSameSite()) {
            $str .= '; samesite=' . $this->getSameSite();
        }

        return $str;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    public function getMaxAge(): int
    {
        return 0 !== $this->expire ? $this->expire - time() : 0;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function isSecure(): bool
    {
        return $this->secure;
    }

    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    public function isCleared(): bool
    {
        return $this->expire < time();
    }

    public function isRaw(): bool
    {
        return $this->raw;
    }

    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }
}
