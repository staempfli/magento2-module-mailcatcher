<?php
/**
 * HandlerFactory
 *
 * @copyright Copyright © 2017 Staempfli AG. All rights reserved.
 * @author    juan.alonso@gmail.com
 */

namespace Staempfli\MailCatcher\Logger\Handler;

use InvalidArgumentException;
use Magento\Framework\ObjectManagerInterface;
use Staempfli\MailCatcher\Logger\Handler\HandlerAbstract as ObjectType;

class HandlerFactory
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $instanceTypeNames = [
        'error' => '\\Staempfli\\MailCatcher\\Logger\\Handler\\Error',
        'info' => '\\Staempfli\\MailCatcher\\Logger\\Handler\\Info',
        'debug' => '\\Staempfli\\MailCatcher\\Logger\\Handler\\Debug',
    ];

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create corresponding class instance
     *
     * @param $type
     * @param array $data
     * @return ObjectType
     */
    public function create($type, array $data = array())
    {
        if (empty($this->instanceTypeNames[$type])) {
            throw new InvalidArgumentException('"' . $type . ': isn\'t allowed');
        }

        $resultInstance = $this->objectManager->create($this->instanceTypeNames[$type], $data);
        if (!$resultInstance instanceof ObjectType) {
            throw new InvalidArgumentException(get_class($resultInstance) . ' isn\'t instance of \Staempfli\MailCatcher\Logger\Handler\HandlerAbstract');
        }

        return $resultInstance;
    }
}