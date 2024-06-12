<?php declare(strict_types=1);

namespace Faslet\Connect\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Debug logger handler class
 */
class Debug extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/faslet-debug.log';
}
