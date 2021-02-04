<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Domain\Model;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use GuzzleHttp\Psr7;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\StreamInterface;

class SerializableUploadedFile implements UploadedFileInterface, \JsonSerializable
{

    /**
     * @var string
     */
    protected $content;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var int
     */
    protected $errorStatus;

    /**
     * @var string
     */
    protected $clientFilename;

    /**
     * @var string
     */
    protected $clientMediaType;

    /**
     * SerializableUploadedFile constructor.
     * @param string $content
     * @param int $size
     * @param int $errorStatus
     * @param string|null $clientFilename
     * @param string|null $clientMediaType
     */
    protected function __construct(string $content, int $size, int $errorStatus, string $clientFilename = null, string $clientMediaType = null)
    {
        $this->content = $content;
        $this->size = $size;
        $this->errorStatus = $errorStatus;
        $this->clientFilename = $clientFilename;
        $this->clientMediaType = $clientMediaType;
    }

    /**
     * @param UploadedFileInterface $uploadedFile
     * @return SerializableUploadedFile
     */
    public static function fromUploadedFile(UploadedFileInterface $uploadedFile): self
    {
        return new static(
            $uploadedFile->getStream()->getContents(),
            $uploadedFile->getSize(),
            $uploadedFile->getError(),
            $uploadedFile->getClientFilename(),
            $uploadedFile->getClientMediaType()
        );
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return Psr7\stream_for($this->content);
    }

    public function moveTo($targetPath)
    {
        Psr7\copy_to_stream(
            $this->getStream(),
            new Psr7\LazyOpenStream($targetPath, 'w')
        );
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->errorStatus;
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
     * @return string
     */
    public function __toString()
    {
        return $this->getClientFilename() ?? '';
    }

    public function jsonSerialize()
    {
        return $this->__toString();
    }
}
