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
use Neos\SymfonyMailer\Service\MailerService;
use Neos\Utility\MediaTypes;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\File;

class EmailAction extends AbstractAction
{
    /**
     * @return ActionResponse|null
     * @throws ActionException
     */
    public function perform(): ?ActionResponse
    {
        if (!class_exists(MailerService::class)) {
            throw new ActionException('The "neos/symfonymailer" doesn\'t seem to be installed, but is required for the EmailAction to work!', 1503392532);
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

        $mail = new Email();
        $mail
            ->addFrom(new Address($senderAddress, $senderName))
            ->subject($subject);

        if (is_array($recipientAddress)) {
            $mail->addTo(...array_map(fn ($entry) => new Address($entry), $recipientAddress));
        } else {
            $mail->addTo(new Address($recipientAddress, $recipientName));
        }

        if ($replyToAddress !== null) {
            $mail->addReplyTo(new Address($replyToAddress));
        }

        if ($carbonCopyAddress !== null) {
            $mail->addCc(new Address($carbonCopyAddress));
        }

        if ($blindCarbonCopyAddress !== null) {
            $mail->addBcc(new Address($blindCarbonCopyAddress));
        }

        if ($text !== null && $html !== null) {
            $mail->html($html);
            $mail->text($text);
        } elseif ($text !== null) {
            $mail->text($text);
        } elseif ($html !== null) {
            $mail->html($html);
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
            $mailer = new MailerService();
            $mailer->getMailer()->send($mail);
        }

        return null;
    }

    /**
     * @param Email $mail
     */
    protected function addAttachments(Email $mail): void
    {
        $attachments = $this->options['attachments'] ?? null;
        if (is_array($attachments)) {
            foreach ($attachments as $attachment) {
                if (is_string($attachment)) {
                    $mail->addPart(new DataPart(new File($attachment)));
                } elseif (is_object($attachment) && ($attachment instanceof UploadedFileInterface)) {
                    $mail->addPart(new DataPart($attachment->getStream()->getContents(), $attachment->getClientFilename(), $attachment->getClientMediaType()));
                } elseif (is_object($attachment) && ($attachment instanceof PersistentResource)) {
                    $stream = $attachment->getStream();
                    if (!is_bool($stream)) {
                        $content = stream_get_contents($stream);
                        if (!is_bool($content)) {
                            $mail->addPart(new DataPart($content, $attachment->getFilename(), $attachment->getMediaType()));
                        }
                    }
                } elseif (is_array($attachment) && isset($attachment['content']) && isset($attachment['name'])) {
                    $content = $attachment['content'];
                    $name = $attachment['name'];
                    $type = $attachment['type'] ?? MediaTypes::getMediaTypeFromFilename($name);
                    $mail->addPart(new DataPart($content, $name, $type));
                }
            }
        }
    }
}
