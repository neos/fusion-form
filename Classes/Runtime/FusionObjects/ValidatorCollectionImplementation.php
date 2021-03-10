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

class ValidatorCollectionImplementation extends AbstractCollectionFusionObject implements ValidatorInterface
{
    protected $itemInterface = ValidatorInterface::class;

    protected $itemPrototype = 'Neos.Fusion.Form:Runtime.Validator';

    /**
     * @param mixed $value
     * @return Result
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     */
    public function validate($value)
    {
        $subValidators = $this->getItems();
        $result = new Result();
        foreach ($subValidators as $subValidator) {
            if ($subValidator instanceof ValidatorInterface) {
                $result->merge($subValidator->validate($value));
            }
        }
        return $result;
    }

    /**
     * @return mixed[]
     */
    public function getOptions()
    {
        return [];
    }
}
