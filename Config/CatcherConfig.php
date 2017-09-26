<?php
/**
 * CatcherConfig
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Config;


use Magento\Framework\App\Config\ScopeConfigInterface;

class CatcherConfig
{
    const XML_PATH = 'staempfli_mailcatcher/configuration/';
    const XML_PATH_ENABLED = self::XML_PATH . 'enabled';
    const XML_PATH_WHITELIST = self::XML_PATH . 'whitelist';
    const XML_PATH_REDIRECT_RECIPIENT = self::XML_PATH . 'redirect_recipient';
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isCatcherEnabled() : bool
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLED);
    }

    public function whiteList() : array
    {
        $whiteListConfig = $this->scopeConfig->getValue(self::XML_PATH_WHITELIST);
        if ($whiteListConfig) {
            return array_map('trim', explode(',', $whiteListConfig));
        }
        return [];
    }

    /**
     * @return mixed
     */
    public function redirectRecipient()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_REDIRECT_RECIPIENT);
    }

}