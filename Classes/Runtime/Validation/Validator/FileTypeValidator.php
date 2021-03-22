<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Validation\Validator;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Validation\Validator\AbstractValidator;
use Neos\Flow\ResourceManagement\PersistentResource;

/**
 * The given $value is valid if it is an \Neos\Flow\ResourceManagement\PersistentResource of the configured resolution
 * Note: a value of NULL or empty string ('') is considered valid
 */
class FileTypeValidator extends AbstractValidator
{
    /**
     * @var mixed[]
     */
    protected $supportedOptions = array(
        'allowedExtensions' => array([], 'Array of allowed file extensions', 'array', false),
        'allowedMediaTypes' => array([], 'Array of allowed media types', 'array', false)
    );

    /**
     * The given $value is valid if it is an \Psr\Http\Message\UploadedFileInterface of the configured resolution
     * Note: a value of NULL or empty string ('') is considered valid
     *
     * @param PersistentResource $resource
     * @return void
     * @api
     */
    protected function isValid($resource)
    {
        if (!$resource instanceof PersistentResource) {
            $this->addError('The given value was not a PersistentResource instance.', 1616425674);
            return;
        }
        if ($this->options['allowedExtensions'] && !in_array($resource->getFileExtension(), $this->options['allowedExtensions'])) {
            $this->addError(
                'The file extension has to be one of "%s", "%s" is not allowed.',
                1616425683,
                [
                    implode(', ', $this->options['allowedExtensions']),
                    $resource->getFileExtension()
                ]
            );
        }
        if ($this->options['allowedMediaTypes'] && !in_array($resource->getMediaType(), $this->options['allowedMediaTypes'])) {
            $this->addError(
                'The media type has to be one of "%s", "%s" is not allowed.',
                1616425912,
                [
                    implode(', ', $this->options['allowedMediaTypes']),
                    $resource->getMediaType()
                ]
            );
        }
    }
}
