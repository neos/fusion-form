<?php
declare(strict_types=1);

namespace Neos\Fusion\Form\Runtime\Action;

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
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Fusion\Form\Runtime\Domain\Exception\ActionException;
use Neos\SwiftMailer\Message as SwiftMailerMessage;
use Neos\Utility\MediaTypes;
use Psr\Http\Message\UploadedFileInterface;

class EmailAction extends AbstractAction
{

    /**
     * @return ActionResponse|null
     * @throws ActionException
     */
    public function perform(): ?ActionResponse
    {
        if (!class_exists(SwiftMailerMessage::class)) {
            throw new ActionException('The "neos/swiftmailer" doesn\'t seem to be installed, but is required for the EmailAction to work!', 1503392532);
        }

        $subject = $this->options['subject'] ?? null;
        $text = $this->options['text'] ?? null;
        $html = $this->options['html'] ?? null;

        $recipientAddress = $this->options['recipientAddress'] ?? null;
        $recipientName = $this->options['recipientName'] ?? null;
        $senderAddress = $this->options['senderAddress'] ?? null;
        $senderName = $this->options['senderName'] ?? null;
        $replyToAddress = $this->options['replyToAddress'] ?? null;
        $carbonCopyAddress = $this->options['carbonCopyAddress'] ?? null;
        $blindCarbonCopyAddress = $this->options['blindCarbonCopyAddress'] ?? null;

        $testMode = $this->options['testMode'] ?? false;

        if ($subject === null) {
            throw new ActionException('The option "subject" must be set for the EmailAction.', 1327060320);
        }
        if ($recipientAddress === null) {
            throw new ActionException('The option "recipientAddress" must be set for the EmailAction.', 1327060200);
        }
        if (is_array($recipientAddress) && !empty($recipientName)) {
            throw new ActionException('The option "recipientName" cannot be used with multiple recipients in the EmailAction.', 1483365977);
        }
        if ($senderAddress === null) {
            throw new ActionException('The option "senderAddress" must be set for the EmailAction.', 1327060210);
        }

        $mail = new SwiftMailerMessage();

        $mail
            ->setFrom($senderName ? [$senderAddress => $senderName] : $senderAddress)
            ->setSubject($subject);

        if (is_array($recipientAddress)) {
            $mail->setTo($recipientAddress);
        } else {
            $mail->setTo($recipientName ? [$recipientAddress => $recipientName] : $recipientAddress);
        }

        if ($replyToAddress !== null) {
            $mail->setReplyTo($replyToAddress);
        }

        if ($carbonCopyAddress !== null) {
            $mail->setCc($carbonCopyAddress);
        }

        if ($blindCarbonCopyAddress !== null) {
            $mail->setBcc($blindCarbonCopyAddress);
        }

        if ($text !== null && $html !== null) {
            $mail->setBody($html, 'text/html');
            $mail->addPart($text, 'text/plain');
        } elseif ($text !== null) {
            $mail->setBody($text, 'text/plain');
        } elseif ($html !== null) {
            $mail->setBody($html, 'text/html');
        }

        $this->addAttachments($mail);

        if ($testMode === true) {
            $response = new ActionResponse();
            $response->setContent(
                /**
                 * @phpstan-ignore-next-line
                 */
                \Neos\Flow\var_dump(
                    [
                        'sender' => [$senderAddress => $senderName],
                        'recipients' => is_array($recipientAddress) ? $recipientAddress : [$recipientAddress => $recipientName],
                        'replyToAddress' => $replyToAddress,
                        'carbonCopyAddress' => $carbonCopyAddress,
                        'blindCarbonCopyAddress' => $blindCarbonCopyAddress,
                        'text' => $text,
                        'html' => $html
                    ],
                    'E-Mail "' . $subject . '"',
                    true
                )
            );
            return $response;
        } else {
            $mail->send();
        }

        return null;
    }

    /**
     * @param SwiftMailerMessage $mail
     */
    protected function addAttachments(SwiftMailerMessage $mail): void
    {
        $attachments = $this->options['attachments'] ?? null;
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (is_string($attachment)) {
                    $mail->attach(\Swift_Attachment::fromPath($attachment));
                } elseif (is_object($attachment) && ($attachment instanceof UploadedFileInterface)) {
                    $mail->attach(new \Swift_Attachment($attachment->getStream()->getContents(), $attachment->getClientFilename(), $attachment->getClientMediaType()));
                } elseif (is_object($attachment) && ($attachment instanceof PersistentResource)) {
                    $stream = $attachment->getStream();
                    if (!is_bool($stream)) {
                        $content = stream_get_contents($stream);
                        if (!is_bool($content)) {
                            $mail->attach(new \Swift_Attachment($content, $attachment->getFilename(), $attachment->getMediaType()));
                        }
                    }
                } elseif (is_array($attachment) && isset($attachment['content']) && isset($attachment['name'])) {
                    $content = $attachment['content'];
                    $name = $attachment['name'];
                    $type =  $attachment['type'] ?? MediaTypes::getMediaTypeFromFilename($name);
                    $mail->attach(new \Swift_Attachment($content, $name, $type));
                }
            }
        }
    }
}
