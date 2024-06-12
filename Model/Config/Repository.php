<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Config;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;

/**
 * Config repository class
 */
class Repository extends System\DataRepository implements ConfigRepositoryInterface
{

    /**
     * @inheritDoc
     */
    public function getExtensionVersion(): string
    {
        return $this->getStoreValue(self::XML_PATH_EXTENSION_VERSION);
    }

    /**
     * @inheritDoc
     */
    public function isDebugMode(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_DEBUG, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_EXTENSION_ENABLE, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getSupportLink(): string
    {
        return self::MODULE_SUPPORT_LINK;
    }

    /**
     * @inheritDoc
     */
    public function getExtensionCode(): string
    {
        return self::EXTENSION_CODE;
    }

    /**
     * @inheritDoc
     */
    public function getStoreUrl(): string
    {
        return $this->getStore()->getBaseUrl();
    }
}
