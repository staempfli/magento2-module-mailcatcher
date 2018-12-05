<?php
/**
 * MailCatcherTransportProxy
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Transport;

use Staempfli\MailCatcher\Mail\Message;
use Magento\Framework\Mail\TransportInterface;
use Staempfli\MailCatcher\Config\CatcherConfig;
use Staempfli\MailCatcher\Logger\MailCatcherLogger;
use Staempfli\MailCatcher\Repository\MailCatcherRepository;

class MailCatcherTransportProxy implements TransportInterface
{
    /**
     * @var MailCatcherLogger
     */
    private $mailCatcherLogger;
    /**
     * @var Message
     */
    private $message;
    /**
     * @var TransportInterface
     */
    private $originalTransport;
    /**
     * @var CatcherConfig
     */
    private $catcherConfig;
    /**
     * @var MailCatcherRepository
     */
    private $mailCatcherRepository;

    public function __construct(
        Message $message,
        MailCatcherLogger $mailCatcherLogger,
        TransportInterface $originalTransport,
        CatcherConfig $catcherConfig,
        MailCatcherRepository $mailCatcherRepository
    ) {
        $this->mailCatcherLogger = $mailCatcherLogger;
        $this->message = $message;
        $this->originalTransport = $originalTransport;
        $this->catcherConfig = $catcherConfig;
        $this->mailCatcherRepository = $mailCatcherRepository;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function sendMessage()
    {
        if ($this->shouldCatchEmail()) {
            $this->mailCatcherLogger->addInfo(
                "Recipients: " . implode(',', $this->message->getRecipients()) . PHP_EOL .
                "Subject: " . $this->message->getSubject() . PHP_EOL .
                "Body: " . $this->getBodyAsString() . PHP_EOL
            );
            return;
        }
        return $this->originalTransport->sendMessage();
    }

    /**
     * @return bool
     */
    public function shouldCatchEmail()
    {
        if (!$this->catcherConfig->isCatcherEnabled()) {
            return false;
        }
        if ($this->areAllRecipientsAllowed($this->message->getRecipients())) {
            return false;
        }
        return true;
    }

    private function areAllRecipientsAllowed(array $recipients): bool
    {
        foreach ($recipients as $recipient) {
            if (!$this->mailCatcherRepository->isRecipientWhiteListed($recipient) &&
                !$this->mailCatcherRepository->isRedirectRecipient($recipient)
            ) {
                return false;
            }
        }
        return true;
    }

    private function getBodyAsString()
    {
        $body = $this->message->getBody();
        if ($body instanceof \Zend\Mime\Message) {
            return $body->generateMessage();
        }
        return $body;
    }

}