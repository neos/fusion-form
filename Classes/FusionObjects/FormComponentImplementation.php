<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\FusionObjects;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Mvc\ActionRequest;
use Neos\Error\Messages\Result;
use Neos\Fusion\Form\Domain\Model\Form;
use Neos\Fusion\FusionObjects\ComponentImplementation;

class FormComponentImplementation extends ComponentImplementation
{
    /**
     * Properties that are ignored and not included into the ``props`` context
     *
     * @var array
     */
    protected $ignoreProperties = ['__meta', 'renderer', 'form'];

    /**
     * Prepare the context for the renderer with the ``form``
     *
     * @param array $context
     * @return array
     */
    protected function prepare($context)
    {
        $request = $this->fusionValue('form/request');
        $fieldNamePrefix = $this->fusionValue('form/fieldNamePrefix');
        $data = $this->fusionValue('form/data');
        $context['form'] = $this->createForm($request, $fieldNamePrefix, $data);

        //
        // push the created `form` to the context before the props are evaluated
        //
        $this->runtime->pushContextArray($context);
        $result = parent::prepare($context);
        $this->runtime->popContext();

        return $result;
    }

    /**
     * Create a form definition object
     *
     * @param ActionRequest|null $request
     * @param string|null $fieldNamePrefix
     * @param mixed|null $data
     * @return Form
     */
    public function createForm(ActionRequest $request = null, string $fieldNamePrefix = null, $data = null): Form
    {
        return new Form(
            $request,
            $data,
            $fieldNamePrefix ?: ($request ? $request->getArgumentNamespace() : ''),
            $request ? $request->getInternalArgument('__submittedArguments') : [],
            $request ? $request->getInternalArgument('__submittedArgumentValidationResults') : new Result()
        );
    }
}
