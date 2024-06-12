<?php declare(strict_types=1);

namespace Faslet\Connect\Logger;

use Magento\Framework\Serialize\Serializer\Json;
use Monolog\Logger;

/**
 * ErrorLogger logger class
 */
class ErrorLogger extends Logger
{

    /**
     * @var Json
     */
    private $json;

    /**
     * ErrorLogger constructor.
     *
     * @param Json $json
     * @param string $name
     * @param array $handlers
     * @param array $processors
     */
    public function __construct(
        Json $json,
        string $name,
        array $handlers = [],
        array $processors = []
    ) {
        $this->json = $json;
        parent::__construct($name, $handlers, $processors);
    }

    /**
     * Add error data to Log
     *
     * @param string $type
     * @param mixed $data
     */
    public function addLog(string $type, $data): void
    {
        if (is_array($data) || is_object($data)) {
            $this->addRecord(static::EMERGENCY, $type . ': ' . $this->json->serialize($data));
        } else {
            $this->addRecord(static::EMERGENCY, $type . ': ' . $data);
        }
    }
}
