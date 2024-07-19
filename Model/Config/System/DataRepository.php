<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Config\System;

use Faslet\Connect\Api\Config\System\DataInterface;

class DataRepository extends BaseRepository implements DataInterface
{

    /**
     * @inheritDoc
     */
    public function getShopId(): ?string
    {
        return $this->getStoreValue(self::SHOP_ID);
    }

    /**
     * @inheritDoc
     */
    public function getAttributes($storeId): array
    {
        return [
            'identifier' => $this->getStoreValue(self::IDENTIFIER_ATTRIBUTE, $storeId),
            'sku' => $this->getStoreValue(self::SKU_ATTRIBUTE, $storeId),
            'brand' => $this->getStoreValue(self::BRAND_ATTRIBUTE, $storeId),
            'size' => $this->getStoreValue(self::SIZE_ATTRIBUTE, $storeId),
            'color' => $this->getStoreValue(self::COLOR_ATTRIBUTE, $storeId)
        ];
    }
}
