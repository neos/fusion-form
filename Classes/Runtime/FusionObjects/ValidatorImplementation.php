<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\FusionObjects;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Error\Messages\Result;
use Neos\Flow\Validation\Validator\ValidatorInterface;
use Neos\Flow\Validation\ValidatorResolver;
use Neos\Fusion\Exception\RuntimeException;
use Neos\Fusion\FusionObjects\AbstractFusionObject;

class ValidatorImplementation extends AbstractFusionObject implements ValidatorInterface
{
    /**
     * @var ValidatorResolver
     * @Flow\Inject
     */
    protected $validatorResolver;

    /**
     * @param mixed $value
     * @return Result
     */
    public function validate($value): Result
    {
        $validator = $this->validatorResolver->createValidator(
            $this->fusionValue('type'),
            $this->fusionValue('options')
        );
        if ($validator === null) {
            throw new \RuntimeException('Validator could not get created.', 1744410020);
        }
        return $validator->validate($value);
    }

    /**
     * Return reference to self during fusion evaluation
     * @return $this
     */
    public function evaluate()
    {
        return $this;
    }

    /**
     * @return mixed[]
     */
    public function getOptions(): array
    {
        return [];
    }
}
