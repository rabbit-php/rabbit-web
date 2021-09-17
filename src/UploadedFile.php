<?php

declare(strict_types=1);

namespace Rabbit\Web;

use Psr\Http\Message\UploadedFileInterface;
use Rabbit\Base\App;

/**
 * Class UploadedFile
 * @package Rabbit\Web
 */
class UploadedFile implements UploadedFileInterface
{
    private static array $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    private string $clientFilename;

    private string $clientMediaType;

    private int $error;

    private ?string $tmpFile;

    private bool $moved = false;

    private int $size;

    public function __construct(
        string $tmpFile,
        int $size,
        int $errorStatus,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ) {
        $this->setError($errorStatus)
            ->setSize($size)
            ->setClientFilename($clientFilename)
            ->setClientMediaType($clientMediaType);
        $this->isOk() && $this->setFile($tmpFile);
    }

    private function setFile(string $file): self
    {
        $this->tmpFile = $file;
        return $this;
    }

    private function setError(int $error): self
    {
        if (false === in_array($error, UploadedFile::$errors)) {
            throw new \InvalidArgumentException('Invalid error status for UploadedFile');
        }

        $this->error = $error;
        return $this;
    }

    private function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    private function setClientFilename(?string $clientFilename): self
    {
        $this->clientFilename = $clientFilename;
        return $this;
    }

    private function setClientMediaType(?string $clientMediaType): self
    {
        $this->clientMediaType = $clientMediaType;
        return $this;
    }

    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function isMoved(): bool
    {
        return $this->moved;
    }

    private function validateActive(): void
    {
        if (false === $this->isOk()) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    public function getStream()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    public function moveTo($targetPath)
    {
        $targetPath = App::getAlias($targetPath);
        $this->validateActive();

        if ($this->tmpFile) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->tmpFile, $targetPath) : move_uploaded_file($this->tmpFile, $targetPath);
        }

        if (!$this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file could not be move to %s', $targetPath));
        }
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getError()
    {
        return $this->error;
    }

    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getClientFilename(),
            'type' => $this->getClientMediaType(),
            'tmp_file' => $this->tmpFile,
            'error' => $this->getError(),
            'size' => $this->getSize(),
        ];
    }

    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
