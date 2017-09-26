<?php
/**
 * HandleMailTransportPlugin
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Plugin;

use Magento\Framework\Mail\TransportInterfaceFactory;
use Staempfli\MailCatcher\Config\CatcherConfig;
use Staempfli\MailCatcher\Transport\MailCatcherTransportProxyFactory;

class HandleMailTransportPlugin
{
    /**
     * @var CatcherConfig
     */
    private $catcherConfig;
    /**
     * @var MailCatcherTransportProxyFactory
     */
    private $mailCatcherTransportProxyFactory;

    public function __construct(
        CatcherConfig $catcherConfig,
        MailCatcherTransportProxyFactory $mailCatcherTransportProxyFactory
    ) {
        $this->catcherConfig = $catcherConfig;
        $this->mailCatcherTransportProxyFactory = $mailCatcherTransportProxyFactory;
    }

    /**
     * @param TransportInterfaceFactory $subject
     * @param callable $proceed
     * @param array $data
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCreate(TransportInterfaceFactory $subject, callable $proceed, array $data = [])
    {
        if ($this->catcherConfig->isCatcherEnabled()) {
            $data['originalTransport'] = $proceed($data);
            return $this->mailCatcherTransportProxyFactory->create($data);
        }
        return $proceed($data);
    }

}