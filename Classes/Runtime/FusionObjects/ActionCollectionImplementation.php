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
use Neos\Flow\Mvc\ActionResponse;
use Neos\Fusion\Core\Parser;
use Neos\Fusion\Core\Runtime;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\Form\Runtime\Domain\Service\ActionResolver;
use Neos\Fusion\Form\Runtime\Domain\ActionInterface;
use Neos\Fusion\Form\Runtime\Helper\SchemaDefinitionToken;
use Neos\Fusion\FusionObjects\DataStructureImplementation;

class ActionCollectionImplementation extends DataStructureImplementation implements ActionInterface
{

    /**
     * @var ActionResolver
     * @Flow\Inject
     */
    protected $actionResolver;

    /**
     * @var ActionInterface[]
     */
    protected $subActions = [];

    public function evaluate()
    {
        $subActionKeys = $this->sortNestedFusionKeys();

        if (count($subActionKeys) === 0) {
            return $this;
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
                $this->subActions[$fieldName] = $subValue;
            } else {
                throw new \InvalidArgumentException('Actions have to implement the ActionInterface ' . $fieldName);
            }
        }
        return $this;
    }

    public function handle(array $data = []): ?ActionResponse
    {
        $response = new ActionResponse();
        foreach ($this->subActions as $subactions) {
            $subActionResponse = $subactions->handle($data);

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
     * Returns TRUE if the given property has no object type assigned
     *
     * @param mixed $property
     * @return bool
     */
    private function isUntypedProperty($property): bool
    {
        if (!is_array($property)) {
            return false;
        }
        return array_intersect_key(array_flip(Parser::$reservedParseTreeKeys), $property) === [];
    }
}
