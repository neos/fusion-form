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
use Neos\Flow\Security\Cryptography\HashService;

class FormStateService
{
    /**
     * @var HashService
     * @Flow\Inject
     */
    protected $hashService;

    /**
     * @param string $string
     * @return FormState
     * @throws \Neos\Flow\Security\Exception\InvalidArgumentForHashGenerationException
     * @throws \Neos\Flow\Security\Exception\InvalidHashException
     */
    public function unserializeState(string $string): FormState
    {
        $validatedState = $this->hashService->validateAndStripHmac($string);
        return unserialize(base64_decode($validatedState), ['allowed_classes' => [FormState::class]]);
    }

    /**
     * @param FormState $state
     * @return string
     */
    public function serializeState(FormState $state): string
    {
        return $this->hashService->appendHmac(base64_encode(serialize($state)));
    }
}
