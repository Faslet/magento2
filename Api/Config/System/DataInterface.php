<?php declare(strict_types=1);

namespace Faslet\Connect\Api\Config\System;

interface DataInterface
{

    /** General Group */
    public const SHOP_ID = 'faslet_connect/general/shop_id';

    /** Data Group */
    public const IDENTIFIER_ATTRIBUTE = 'faslet_connect/data/identifier_attribute';
    public const SKU_ATTRIBUTE = 'faslet_connect/data/sku_attribute';
    public const BRAND_ATTRIBUTE = 'faslet_connect/data/brand_attribute';
    public const SIZE_ATTRIBUTE = 'faslet_connect/data/size_attribute';
    public const COLOR_ATTRIBUTE = 'faslet_connect/data/color_attribute';

    /**
     * @return string|null
     */
    public function getShopId(): ?string;

    /**
     * @return array
     */
    public function getAttributes($storeId): array;
}
