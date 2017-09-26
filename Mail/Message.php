<?php
/**
 * Message
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Mail;

use Magento\Framework\Mail\MessageInterface;
use Staempfli\MailCatcher\Config\CatcherConfig;
use Staempfli\MailCatcher\Repository\MailCatcherRepository;

class Message extends \Magento\Framework\Mail\Message implements MessageInterface
{
    /**
     * @var CatcherConfig
     */
    private $catcherConfig;
    /**
     * @var MailCatcherRepository
     */
    private $mailCatcherRepository;

    public function __construct(
        CatcherConfig $catcherConfig,
        MailCatcherRepository $mailCatcherRepository,
        $charset = 'utf-8'
    ) {
        $this->catcherConfig = $catcherConfig;
        $this->mailCatcherRepository = $mailCatcherRepository;
        parent::__construct($charset);
    }

    /**
     * {@inheritdoc}
     */
    public function AddTo($address, $name = '')
    {
        $redirectAddress = $this->getRedirectRecipient($address);
        if ($redirectAddress) {
            $address = $redirectAddress;
        }
        parent::addTo($address, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function addCc($address, $name = '')
    {
        $redirectAddress = $this->getRedirectRecipient($address);
        if ($redirectAddress) {
            $address = $redirectAddress;
        }
        return parent::addCc($address, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function addBcc($address)
    {
        $redirectAddress = $this->getRedirectRecipient($address);
        if ($redirectAddress) {
            $address = $redirectAddress;
        }
        return parent::addBcc($address);
    }

    /**
     * @param $address
     * @return bool|string|array
     */
    private function getRedirectRecipient($address)
    {
        if ($this->catcherConfig->isCatcherEnabled()) {
            $redirectRecipient = $this->catcherConfig->redirectRecipient();
            if ($redirectRecipient) {
                return $this->getAddressWithRedirectRecipient($address, $redirectRecipient);
            }
        }
        return false;
    }

    /**
     * @param $address
     * @param $redirectRecipient
     * @return array|string
     */
    private function getAddressWithRedirectRecipient($address, $redirectRecipient)
    {
        if (is_array($address)) {
            foreach ($address as &$email) {
                if (!$this->mailCatcherRepository->isRecipientWhiteListed($email)) {
                    $email = $redirectRecipient;
                }
            }
        }
        if (is_string($address)) {
            if (!$this->mailCatcherRepository->isRecipientWhiteListed($address)) {
                $address = $redirectRecipient;
            }
        }
        return $address;
    }

}