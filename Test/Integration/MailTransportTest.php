<?php
/**
 * MailTransportTest
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Test\Integration;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\TransportInterface;
use Magento\Store\Model\Store;
use Magento\TestFramework\Helper\Bootstrap;
use Staempfli\MailCatcher\Config\CatcherConfig;
use Staempfli\MailCatcher\Transport\MailCatcherTransport;
use Staempfli\MailCatcher\Transport\MailCatcherTransportProxy;

class MailTransportTest extends \PHPUnit\Framework\TestCase
{
    const TEST_EMAIL_IDENTIFIER = 'staempfli_mailcatcher_email_test_template';

    public static $catchEmails = [
        'catch_one@mail.catcher',
        'catch_two@mail.catcher',
    ];

    private $catchCcEmail = 'catch_one_cc@mail.catcher';
    private $catchBccEmail = 'catch_one_bcc@mail.catcher';

    public static $whiteListEmails = [
        'whitelist_one@mail.catcher',
        'whitelist_two@mail.catcher',
    ];

    public static $whiteListDomains = [
        'whitelist.catcher',
    ];

    private $whiteListEmailsByDomain = [
        'domain_whitelist_one@whitelist.catcher',
        'domain_whitelist_two@whitelist.catcher',
    ];

    public static $redirectRecipient = 'redirect_recipient@mail.catcher';

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->transportBuilder = $objectManager->create(TransportBuilderFake::class);
    }

    public static function loadEnableCatcherConfiguration()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $config->saveConfig(CatcherConfig::XML_PATH_ENABLED, 1, 'default', 0);
        $config->saveConfig(CatcherConfig::XML_PATH_WHITELIST, '', 'default', 0);
        $config->saveConfig(CatcherConfig::XML_PATH_REDIRECT_RECIPIENT, '', 'default', 0);
    }

    public static function loadDisableCatcherConfiguration()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $config->saveConfig(CatcherConfig::XML_PATH_ENABLED, 0, 'default', 0);
    }

    public static function loadEnableWithWhitelistCatcherConfiguration()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $config->saveConfig(CatcherConfig::XML_PATH_ENABLED, 1, 'default', 0);
        $whiteListConfig = implode(',', array_merge(self::$whiteListEmails, self::$whiteListDomains));
        $config->saveConfig(CatcherConfig::XML_PATH_WHITELIST, $whiteListConfig, 'default', 0);
    }

    public static function loadEnableWithWhitelistAndRedirectCatcherConfiguration()
    {
        $config = ObjectManager::getInstance()->get(Config::class);
        $config->saveConfig(CatcherConfig::XML_PATH_ENABLED, 1, 'default', 0);
        $whiteListConfig = implode(',', array_merge(self::$whiteListEmails, self::$whiteListDomains));
        $config->saveConfig(CatcherConfig::XML_PATH_WHITELIST, $whiteListConfig, 'default', 0);
        $config->saveConfig(CatcherConfig::XML_PATH_REDIRECT_RECIPIENT, self::$redirectRecipient, 'default', 0);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     */
    public function testCatchEmailsEnabled()
    {
        $this->assertInstanceOf(MailCatcherTransportProxy::class, $this->getMailTransport(['some-email@test.com']));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 0
     */
    public function testCatchEmailsDisabled()
    {
        $this->assertNotInstanceOf(MailCatcherTransportProxy::class, $this->getMailTransport(['some-email@test.com']));
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     */
    public function testCatchAllEmails()
    {
        foreach (self::$catchEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
        foreach (self::$whiteListEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
        foreach ($this->whiteListEmailsByDomain as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     */
    public function testCatchOnlyNotWhitelistedEmails()
    {
        foreach (self::$catchEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
        foreach (self::$whiteListEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertFalse($mailTransport->shouldCatchEmail());
        }
        foreach ($this->whiteListEmailsByDomain as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertFalse($mailTransport->shouldCatchEmail());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/redirect_recipient redirect_recipient@mail.catcher
     */
    public function testNotCatchRedirectedEmails()
    {
        foreach (self::$catchEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertFalse($mailTransport->shouldCatchEmail());
            $recipients = $mailTransport->getMessage()->getRecipients();
            $this->assertEquals(self::$redirectRecipient, reset($recipients));
        }
        foreach (self::$whiteListEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertFalse($mailTransport->shouldCatchEmail());
            $recipients = $mailTransport->getMessage()->getRecipients();
            $this->assertEquals($email, reset($recipients));
        }
        foreach ($this->whiteListEmailsByDomain as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email]);
            $this->assertFalse($mailTransport->shouldCatchEmail());
            $recipients = $mailTransport->getMessage()->getRecipients();
            $this->assertEquals($email, reset($recipients));
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     */
    public function testSeveralCatchAllEmails()
    {
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(self::$catchEmails);
        $this->assertTrue($mailTransport->shouldCatchEmail());
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(self::$whiteListEmails);
        $this->assertTrue($mailTransport->shouldCatchEmail());
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport($this->whiteListEmailsByDomain);
        $this->assertTrue($mailTransport->shouldCatchEmail());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     */
    public function testSeveralCatchOnlyNotWhitelistedEmails()
    {
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(self::$catchEmails);
        $this->assertTrue($mailTransport->shouldCatchEmail());

        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(array_merge(self::$whiteListEmails, $this->whiteListEmailsByDomain));
        $this->assertFalse($mailTransport->shouldCatchEmail());

        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(
            array_merge(self::$whiteListEmails, self::$catchEmails, $this->whiteListEmailsByDomain)
        );
        $this->assertTrue($mailTransport->shouldCatchEmail());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/redirect_recipient redirect_recipient@mail.catcher
     */
    public function testSeveralNotCatchRedirectedEmails()
    {
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(self::$catchEmails);
        $this->assertFalse($mailTransport->shouldCatchEmail());
        foreach ($mailTransport->getMessage()->getRecipients() as $recipient) {
            $this->assertEquals(self::$redirectRecipient, $recipient);
        }

        $allWhiteListedEmails = array_merge(self::$whiteListEmails, $this->whiteListEmailsByDomain);
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport($allWhiteListedEmails);
        $this->assertFalse($mailTransport->shouldCatchEmail());
        foreach ($mailTransport->getMessage()->getRecipients() as $recipient) {
            $this->assertContains($recipient, $allWhiteListedEmails);
        }

        $allWhiteListedEmails = array_merge(self::$whiteListEmails, $this->whiteListEmailsByDomain);
        /** @var MailCatcherTransportProxy $mailTransport */
        $mailTransport = $this->getMailTransport(array_merge($allWhiteListedEmails, self::$catchEmails));
        $this->assertFalse($mailTransport->shouldCatchEmail());
        $validEmailsToSend = array_merge($allWhiteListedEmails, [self::$redirectRecipient]);
        foreach ($mailTransport->getMessage()->getRecipients() as $recipient) {
            $this->assertContains($recipient, $validEmailsToSend);
        }
        $oneWhitelistedEmail = reset($allWhiteListedEmails);
        $this->assertContains($oneWhitelistedEmail, $mailTransport->getMessage()->getRecipients());
        $this->assertContains(self::$redirectRecipient, $mailTransport->getMessage()->getRecipients());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     */
    public function testCatchCcEmails()
    {
        foreach (self::$catchEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email], [$this->catchCcEmail]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
        foreach (self::$whiteListEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email], [$this->catchCcEmail]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/enabled 1
     * @magentoConfigFixture default/staempfli_mailcatcher/configuration/whitelist whitelist_one@mail.catcher,whitelist_two@mail.catcher,whitelist.catcher
     */
    public function testCatchBccEmails()
    {
        foreach (self::$catchEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email], [$this->catchBccEmail]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
        foreach (self::$whiteListEmails as $email) {
            /** @var MailCatcherTransportProxy $mailTransport */
            $mailTransport = $this->getMailTransport([$email], [$this->catchBccEmail]);
            $this->assertTrue($mailTransport->shouldCatchEmail());
        }
    }

    private function getMailTransport(array $recipients, array $cc = [], array $bcc = []): TransportInterface
    {
        $transport = $this->transportBuilder
            ->setTemplateIdentifier(self::TEST_EMAIL_IDENTIFIER)
            ->setTemplateOptions(['area' => FrontNameResolver::AREA_CODE, 'store' => Store::DEFAULT_STORE_ID])
            ->setTemplateVars([])
            ->setFrom(['name' => 'integration-tests', 'email' => 'integration-test@mail.catcher'])
            ->addTo($recipients);
        if ($cc) {
            $transport->addCc($cc);
        }
        if ($bcc) {
            $transport->addBcc($bcc);
        }
        return $transport->getTransport();
    }

}