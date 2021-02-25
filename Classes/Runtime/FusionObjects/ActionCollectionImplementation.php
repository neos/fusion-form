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

use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;

class ActionCollectionImplementation extends AbstractCollectionFusionObject implements ActionInterface
{

    /**
     * @return $this
     */
    public function evaluate(): self
    {
        return $this;
    }

    /**
     * @return ActionResponse|null
     */
    public function perform(): ?ActionResponse
    {
        $response = new ActionResponse();
        $subActions = $this->getSubActions();
        foreach ($subActions as $subactions) {
            $subActionResponse = $subactions->perform();
            if ($subActionResponse) {
                // content of multiple responses is concatenated
                if ($subActionResponse->getContent()) {
                    $mergedContent = $response->getContent() . $subActionResponse->getContent();
                    $subActionResponse->setContent($mergedContent);
                }
                // preserve non 200 status codes that would otherwise be overwritten
                if ($response->getStatusCode() !== 200 && $subActionResponse->getStatusCode() == 200) {
                    $subActionResponse->setStatusCode($response->getStatusCode());
                }
                $subActionResponse->mergeIntoParentResponse($response);
            }
        }
        return $response;
    }

    /**
     * @return ActionInterface[]
     * @throws \Neos\Flow\Configuration\Exception\InvalidConfigurationException
     * @throws \Neos\Flow\Mvc\Exception\StopActionException
     * @throws \Neos\Flow\Security\Exception
     * @throws \Neos\Fusion\Exception
     */
    protected function getSubActions(): array
    {
        $subActions = [];
        $subActionKeys = $this->sortNestedFusionKeys();

        if (count($subActionKeys) === 0) {
            return [];
        }

        $subValues = [];
        foreach ($subActionKeys as $key) {
            $propertyPath = $key;
            if ($this->isUntypedProperty($this->properties[$key])) {
                $propertyPath .= '<Neos.Fusion.Form:Runtime.Action>';
            }
            try {
                $value = $this->fusionValue($propertyPath);
            } catch (\Exception $e) {
                $value = $this->runtime->handleRenderingException($this->path . '/' . $key, $e);
            }
            if ($value === null && $this->runtime->getLastEvaluationStatus() === Runtime::EVALUATION_SKIPPED) {
                continue;
            }
            $subValues[$key] = $value;
        }

        foreach ($subValues as $fieldName => $subValue) {
            if ($subValue instanceof ActionInterface) {
                $subActions[$fieldName] = $subValue;
            } else {
                throw new \InvalidArgumentException('Actions have to implement the ActionInterface ' . $fieldName);
            }
        }

        return $subActions;
    }
}
