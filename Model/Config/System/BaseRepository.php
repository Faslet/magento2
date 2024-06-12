<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Config\System;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class BaseRepository
{

    /**
     * @var Json
     */
    protected $json;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var ProductMetadata
     */
    protected $metadata;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * BaseRepository constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param ProductMetadata $metadata
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Json $json,
        ProductMetadata $metadata,
        StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->metadata = $metadata;
        $this->storeManager = $storeManager;
    }

    /**
     * Retrieve config value by path, storeId and scope
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     * @return string|null
     */
    protected function getStoreValue(string $path, ?int $storeId = null, ?string $scope = null): ?string
    {
        if (!$scope) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->getValue($path, $scope, $storeId);
    }

    /**
     * Retrieve config flag by path, storeId and scope
     *
     * @param string $path
     * @param int|null $storeId
     * @param string|null $scope
     * @return bool
     */
    protected function isSetFlag(string $path, ?int $storeId = null, ?string $scope = null): bool
    {
        if (empty($scope)) {
            $scope = ScopeInterface::SCOPE_STORE;
        }

        return $this->scopeConfig->isSetFlag($path, $scope, $storeId);
    }

    /**
     * @return StoreInterface
     */
    protected function getStore(): StoreInterface
    {
        try {
            return $this->storeManager->getStore();
        } catch (\Exception $e) {
            if ($store = $this->storeManager->getDefaultStoreView()) {
                return $store;
            }
        }
        $stores = $this->storeManager->getStores();
        return reset($stores);
    }
}
