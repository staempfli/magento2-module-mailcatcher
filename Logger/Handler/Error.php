<?php
/**
 * Error
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@gmail.com
 */

namespace Staempfli\MailCatcher\Logger\Handler;

use Monolog\Logger;

class Error extends HandlerAbstract
{
    /**
     * @var string
     */
    protected $fileName = 'error.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;
}
