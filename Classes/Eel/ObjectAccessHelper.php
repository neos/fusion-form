<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Eel;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Utility\ObjectAccess;

class ObjectAccessHelper implements ProtectedContextAwareInterface
{


    /**
     * @param mixed $subject An object or array
     * @param string $propertyPath
     * @return mixed Value of the property
     */
    public function getPropertyPath($subject, string $propertyPath = null)
    {
        return ObjectAccess::getPropertyPath($subject, $propertyPath);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }

}
