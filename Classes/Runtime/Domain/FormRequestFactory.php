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
            $subrequestArguments = [];
            if ($submittedData['__trustedProperties']) {
                $trustedProperties = unserialize($this->hashService->validateAndStripHmac($submittedData['__trustedProperties']), ['allowed_classes' => false]);
                foreach ($trustedProperties as $field => $number) {
                    if (array_key_exists($field, $submittedData)) {
                        $subrequestArguments[$field] = $submittedData[$field];
                    }
                }
            }
            $formRequest->setArguments($subrequestArguments);
        }
        return $formRequest;
    }
}
