<?php
/**
 * HandlerAbstract
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@gmail.com
 */

namespace Staempfli\MailCatcher\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;

class HandlerAbstract extends Base
{
    /**
     * HandlerAbstract constructor.
     *
     * Set default filePath for MailCatcher logs folder
     *
     * @param DriverInterface $filesystem
     * @param null|string $filePath
     */
    public function __construct(DriverInterface $filesystem, $filePath = BP . '/var/log/mailcatcher/') //@codingStandardsIgnoreLine
    {
        parent::__construct($filesystem, $filePath);
    }
}
