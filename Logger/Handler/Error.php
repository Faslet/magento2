<?php declare(strict_types=1);

namespace Faslet\Connect\Logger\Handler;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Error logger handler class
 */
class Error extends Base
{

    /**
     * @var int
     */
    protected $loggerType = Logger::ERROR;

    /**
     * @var string
     */
    protected $fileName = '/var/log/faslet-error.log';
}
