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

    /**
     * @var int[]
     */
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

    /**
     * @var string
     */
    private string $clientFilename;

    /**
     * @var string
     */
    private string $clientMediaType;

    /**
     * @var int
     */
    private int $error;

    /**
     * @var null|string
     */
    private ?string $tmpFile;

    /**
     * @var bool
     */
    private bool $moved = false;

    /**
     * @var int
     */
    private int $size;

    /**
     * @param string $tmpFile
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    public function __construct(
        string $tmpFile,
        int $size,
        int $errorStatus,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    )
    {
        $this->setError($errorStatus)
            ->setSize($size)
            ->setClientFilename($clientFilename)
            ->setClientMediaType($clientMediaType);
        $this->isOk() && $this->setFile($tmpFile);
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param string $file
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setFile(string $file)
    {
        $this->tmpFile = $file;
        return $this;
    }

    /**
     * @param int $error
     * @return $this
     */
    private function setError(int $error)
    {
        if (false === in_array($error, UploadedFile::$errors)) {
            throw new \InvalidArgumentException('Invalid error status for UploadedFile');
        }

        $this->error = $error;
        return $this;
    }

    /**
     * @param int $size
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setSize(int $size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @param string|null $clientFilename
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setClientFilename(?string $clientFilename)
    {
        $this->clientFilename = $clientFilename;
        return $this;
    }

    /**
     * @param string|null $clientMediaType
     * @return $this
     * @throws \InvalidArgumentException
     */
    private function setClientMediaType(?string $clientMediaType)
    {
        $this->clientMediaType = $clientMediaType;
        return $this;
    }

    /**
     * Return true if there is no upload error
     *
     * @return boolean
     */
    private function isOk(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @return boolean
     */
    public function isMoved(): bool
    {
        return $this->moved;
    }

    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new \RuntimeException('Cannot retrieve stream due to upload error');
        }

        if ($this->isMoved()) {
            throw new \RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }

    /**
     * @return \Psr\Http\Message\StreamInterface|void
     */
    public function getStream()
    {
        throw new \BadMethodCallException('Not implemented');
    }

    /**
     * @param string $targetPath
     */
    public function moveTo($targetPath)
    {
        $targetPath = App::getAlias($targetPath);
        $this->validateActive();

        if (!$this->isStringNotEmpty($targetPath)) {
            throw new \InvalidArgumentException('Invalid path provided for move operation');
        }

        if ($this->tmpFile) {
            $this->moved = php_sapi_name() == 'cli' ? rename($this->tmpFile, $targetPath) : move_uploaded_file($this->tmpFile, $targetPath);
        }

        if (!$this->moved) {
            throw new \RuntimeException(sprintf('Uploaded file could not be move to %s', $targetPath));
        }
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return int
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @return string|null
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @return string|null
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode($this->toArray());
    }
}
