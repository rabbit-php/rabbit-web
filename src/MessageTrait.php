<?php
declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\StreamInterface;

/**
 * Trait MessageTrait
 * @package rabbit\web
 */
trait MessageTrait
{
    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var string
     */
    protected string $protocol = '1.1';

    /**
     * @var StreamInterface
     */
    protected ?StreamInterface $stream = null;

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * @param $version
     * @return $this
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $clone = &$this;
        $clone->protocol = $version;
        return $clone;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * @param $name
     * @return array
     */
    public function getHeader($name): array
    {
        $name = strtolower($name);
        return isset($this->headers[$name]) ? $this->headers[$name] : [];
    }

    /**
     * @param $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        return implode(', ', $this->getHeader($name));
    }

    /**
     * @param $name
     * @param $value
     * @return MessageTrait
     */
    public function withHeader($name, $value)
    {
        $normalized = strtolower($name);
        if (!is_array($value)) {
            $value = [$value];
        }
        $value = $this->trimHeaderValues($value);
        $clone = &$this;
        $clone->headers[$normalized] = $value;

        return $clone;
    }

    /**
     * @param array $headers
     * @return static
     */
    public function withHeaders(array $headers)
    {
        $clone = &$this;
        foreach ($headers as $name => $value) {
            $clone = $clone->withHeader(str_replace('_', '-', $name), $value);
        }
        return $clone;
    }

    /**
     * @param $name
     * @param $value
     * @return MessageTrait
     */
    public function withAddedHeader($name, $value)
    {
        $normalized = strtolower($name);

        $clone = &$this;
        $clone->headers[$name] = $value;
        return $clone;
    }

    /**
     * @param $name
     * @return $this|MessageTrait
     */
    public function withoutHeader($name)
    {
        $normalized = strtolower($name);

        if (!isset($this->headers[$normalized])) {
            return $this;
        }

        $clone = &$this;
        unset($clone->headers[$normalized]);

        return $clone;
    }

    /**
     * @param array $headers
     * @return $this
     */
    private function setHeaders(array $headers)
    {
        $this->headerNames = $this->headers = [];
        foreach ($headers as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }

            $value = $this->trimHeaderValues($value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
        return $this;
    }

    /**
     * @return StreamInterface|SwooleStream
     */
    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = new SwooleStream('');
        }

        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     * @return $this|MessageTrait
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }

        $clone = &$this;
        $clone->stream = $body;
        return $clone;
    }

    /**
     * @param array $values
     * @return array
     */
    private function trimHeaderValues(array $values)
    {
        return array_map(function ($value) {
            return trim($value, " \t");
        }, $values);
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        return $this->getHeaderLine('Content-Type');
    }

    /**
     * @return bool
     */
    public function isMultipart()
    {
        try {
            return stripos($this->getContentType(), 'multipart/') === 0;
        } catch (\ExceptionInterface $e) {
            return false;
        }
    }
}
