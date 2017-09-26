<?php
/**
 * Info
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@gmail.com
 */

namespace Staempfli\MailCatcher\Logger\Handler;

use Monolog\Logger;

class Info extends HandlerAbstract
{
    /**
     * @var string
     */
    protected $fileName = 'info.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;
}
