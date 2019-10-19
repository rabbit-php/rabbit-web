<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/9
 * Time: 17:57
 */

namespace rabbit\web;

/**
 * Class Cookie
 * @package rabbit\web
 */
class Cookie
{
    protected $name;
    protected $value;
    protected $domain;
    protected $expire;
    protected $path;
    protected $secure;
    protected $httpOnly;
    private $raw;
    private $sameSite;

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
     * @param $name
     * @param null $value
     * @param int $expire
     * @param string $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @param bool $raw
     * @param null $sameSite
     */
    public function __construct(
        $name,
        $value = null,
        $expire = 0,
        $path = '/',
        $domain = null,
        $secure = false,
        $httpOnly = true,
        $raw = false,
        $sameSite = null
    ) {
        // from PHP source code
        if (preg_match("/[=,; \t\r\n\013\014]/", $name)) {
            throw new \InvalidArgumentException(sprintf('The cookie name "%s" contains invalid characters.', $name));
        }

        if (empty($name)) {
            throw new \InvalidArgumentException('The cookie name cannot be empty.');
        }

        // convert expiration time to a Unix timestamp
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->format('U');
        } elseif (!is_numeric($expire)) {
            $expire = strtotime($expire);

            if (false === $expire) {
                throw new \InvalidArgumentException('The cookie expiration time is not valid.');
            }
        }

        $this->name = $name;
        $this->value = $value;
        $this->domain = $domain;
        $this->expire = 0 < $expire ? (int)$expire : 0;
        $this->path = empty($path) ? '/' : $path;
        $this->secure = (bool)$secure;
        $this->httpOnly = (bool)$httpOnly;
        $this->raw = (bool)$raw;

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
