<?php
/**
 * MailCatcherRepository
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Repository;

use Staempfli\MailCatcher\Config\CatcherConfig;

class MailCatcherRepository
{
    /**
     * @var CatcherConfig
     */
    private $catcherConfig;

    public function __construct(
        CatcherConfig $catcherConfig
    ) {
        $this->catcherConfig = $catcherConfig;
    }

    public function isRecipientWhiteListed(string $recipient) : bool
    {
        if (in_array($recipient, $this->catcherConfig->whiteList())) {
            return true;
        }
        if ($this->isRecipientDomainInWhitelist($recipient)) {
            return true;
        }
        return false;
    }

    private function isRecipientDomainInWhitelist(string $recipient) : bool
    {
        $emailParts = explode('@', $recipient);
        $recipientDomain = array_pop($emailParts);
        if (in_array($recipientDomain, $this->catcherConfig->whiteList())) {
            return true;
        }
        return false;
    }

    public function isRedirectRecipient(string $recipient) : bool
    {
        $redirectRecipient = $this->catcherConfig->redirectRecipient();
        return $recipient === $redirectRecipient;
    }
}