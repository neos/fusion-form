<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Domain;

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
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Security\Cryptography\HashService;

class FormRequestFactory
{
    /**
     * @var HashService
     * @Flow\Inject
     */
    protected $hashService;

    /**
     * Prepare subrequest for the identifier namespace and transfer the arguments
     * only arguments present in __trustedProperties are transferred
     *
     * @param ActionRequest $parentRequest
     * @param string $identifier
     * @return ActionRequest
     */
    public function createFormRequest(ActionRequest $parentRequest, string $identifier): ActionRequest
    {
        $formRequest = $parentRequest->createSubRequest();
        $formRequest->setArgumentNamespace($identifier);
        if ($parentRequest->hasArgument($identifier) === true && is_array($parentRequest->getArgument($identifier))) {
            $submittedData = $parentRequest->getArgument($identifier);
            if ($submittedData['__trustedProperties']) {
                $trustedProperties = unserialize($this->hashService->validateAndStripHmac($submittedData['__trustedProperties']), ['allowed_classes' => false]);
                $subrequestArguments = $this->filterSubmittedDataWithTrustedProperties($submittedData, $trustedProperties);
            } else {
                $subrequestArguments = [];
            }
            $formRequest->setArguments($subrequestArguments);
        }
        return $formRequest;
    }

    /**
     * Filter incoming data with the trusted properties data-structure recursively this ensures only values that
     * where actually rendered by the form are passed as result to the form process
     *
     * @param mixed[] $submittedData
     * @param mixed[] $trustedProperties
     * @return mixed[]
     * @throws \Exception
     */
    protected function filterSubmittedDataWithTrustedProperties($submittedData, array $trustedProperties): array
    {
        $filteredData = [];
        if (!is_array($submittedData)) {
            return $filteredData;
        }
        foreach ($trustedProperties as $fieldName => $trustedProperty) {
            if (array_key_exists($fieldName, $submittedData)) {
                if ($trustedProperty === 1) {
                    $filteredData[$fieldName] = $submittedData[$fieldName];
                } elseif (is_array($trustedProperty)) {
                    $filteredData[$fieldName] = $this->filterSubmittedDataWithTrustedProperties($submittedData[$fieldName], $trustedProperty);
                } else {
                    throw new \Exception('This exception should never be thrown as trusted properties are either arrays or 1');
                }
            }
        }
        return $filteredData;
    }
}
