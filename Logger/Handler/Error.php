<?php declare(strict_types=1);

namespace Faslet\Connect\Logger\Handler;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Error extends StreamHandler
{
    public const FILENAME = 'faslet-error.log';
    public const LEVEL = Logger::ERROR;

    public function __construct()
    {
        /** @phpstan-ignore constant.notFound */
        parent::__construct(BP . '/var/log/' . self::FILENAME, self::LEVEL);
    }
}
