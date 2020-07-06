<?php
declare(strict_types=1);

namespace Rabbit\Web;

/**
 * Class Cookie
 * @package Rabbit\Web
 */
class Cookie
{
    /** @var string */
    protected string $name;
    /** @var string|null */
    protected string $value;
    /** @var string|null */
    protected string $domain;
    /** @var int */
    protected int $expire;
    /** @var string */
    protected string $path;
    /** @var bool */
    protected bool $secure;
    /** @var bool */
    protected bool $httpOnly;
    /** @var bool */
    private bool $raw;
    /** @var mixed|string */
    private string $sameSite;

    const SAMESITE_LAX = 'lax';
    const SAMESITE_STRICT = 'strict';

    /**
     * @param $cookie
     * @param bool $decode
     * @return static
     */
    public static function fromString($cookie, $decode = false)
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

        return new static($data['name'], $data['value'], $data['expires'], $data['path'], $data['domain'],
            $data['secure'], $data['httponly'], $data['raw'], $data['samesite']);
    }

    /**
     * Cookie constructor.
     * @param string $name
     * @param string|null $value
     * @param int $expire
     * @param string $path
     * @param string|null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param string|null $sameSite
     */
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
    )
    {
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

    /**
     * @return string
     */
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

    /**
     * @return mixed
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return null
     */
    public function getValue(): ?string
    {
        return $this->value;
    }

    /**
     * @return null|string
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * @return int
     */
    public function getExpiresTime(): int
    {
        return $this->expire;
    }

    /**
     * @return int
     */
    public function getMaxAge(): int
    {
        return 0 !== $this->expire ? $this->expire - time() : 0;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @return bool
     */
    public function isHttpOnly(): bool
    {
        return $this->httpOnly;
    }

    /**
     * @return bool
     */
    public function isCleared(): bool
    {
        return $this->expire < time();
    }

    /**
     * @return bool
     */
    public function isRaw(): bool
    {
        return $this->raw;
    }

    /**
     * @return null|string
     */
    public function getSameSite(): ?string
    {
        return $this->sameSite;
    }
}
