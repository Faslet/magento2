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
    public function getAttributes(): array
    {
        return [
            'identifier' => $this->getStoreValue(self::IDENTIFIER_ATTRIBUTE),
            'sku' => $this->getStoreValue(self::SKU_ATTRIBUTE),
            'brand' => $this->getStoreValue(self::BRAND_ATTRIBUTE),
            'size' => $this->getStoreValue(self::SIZE_ATTRIBUTE),
            'color' => $this->getStoreValue(self::COLOR_ATTRIBUTE)
        ];
    }
}
