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
    public function isDebugMode(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_DEBUG, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function isEnabled(?int $storeId = null): bool
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

    /**
     * @inheritDoc
     */
    public function isRmaExportEnabled(?int $storeId = null): bool
    {
        return $this->isSetFlag(self::XML_PATH_RMA_EXPORT_ENABLE, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getRmaEndpointUrl(?int $storeId = null): ?string
    {
        return $this->getStoreValue(self::XML_PATH_RMA_ENDPOINT_URL, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getRmaAuthToken(?int $storeId = null): ?string
    {
        return $this->getDecryptedStoreValue(self::XML_PATH_RMA_AUTH_TOKEN, $storeId);
    }

    /**
     * @inheritDoc
     */
    public function getRmaTimeout(?int $storeId = null): int
    {
        $timeout = (int)$this->getStoreValue(self::XML_PATH_RMA_TIMEOUT, $storeId);

        return $timeout > 0 ? $timeout : self::DEFAULT_RMA_TIMEOUT;
    }

    /**
     * @inheritDoc
     */
    public function getRmaLifecycleEvents(?int $storeId = null): array
    {
        $rawValue = (string)$this->getStoreValue(self::XML_PATH_RMA_LIFECYCLE_EVENTS, $storeId);
        if ($rawValue === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $rawValue))));
    }

    /**
     * @inheritDoc
     */
    public function shouldExportRmaLifecycleEvent(string $eventCode, ?int $storeId = null): bool
    {
        return in_array($eventCode, $this->getRmaLifecycleEvents($storeId), true);
    }
}
