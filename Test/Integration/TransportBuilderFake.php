<?php
/**
 * TransportBuilderFake
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@staempfli.com
 */

namespace Staempfli\MailCatcher\Test\Integration;

use Magento\Framework\Mail\Template\TransportBuilder;

class TransportBuilderFake extends TransportBuilder
{
    /**
     * This fake class is created for test purposes.
     * Magento overwrites the preference for `TransportBuilder` on the objectManager used for tests. See:
     * - magento2-base/dev/tests/integration/framework/Magento/TestFramework/Application.php Line 345
     * - 'preferences' => [
     *       'Magento\Framework\Mail\Template\TransportBuilder' =>
     *          'Magento\TestFramework\Mail\Template\TransportBuilderMock',
     * ]
     *
     * Because of that, "TransportBuilderMock" is always returned on tests.
     * For out tests we need an instance of original "TransportBuilder", so the only way to accomplish that
     * is creating this fake class that directly extends from it.
     */
}