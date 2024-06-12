<?php declare(strict_types=1);

namespace Faslet\Connect\ViewModel;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * ProductWidget view model
 */
class ProductWidget implements ArgumentInterface
{
    /** @var null|ProductInterface */
    private $product = null;
    /**
     * @var array
     */
    private $attributes = [];
    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;
    /**
     * @var ImageFactory
     */
    private $imageHelperFactory;
    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ImageFactory $imageHelperFactory,
        ConfigProvider $configProvider,
        CatalogHelper $catalogHelper
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->configProvider = $configProvider;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @return array[]
     */
    public function getWidgetData(): ?array
    {
        $product = $this->getCurrentProduct();
        $shopId = $this->configProvider->getShopId();

        if (!$this->isEnabled() || !$shopId || $product->getTypeId() !== Configurable::TYPE_CODE) {
            return null;
        }

        return [
            'shop' => [
                'id' => $shopId,
                'url' => $this->configProvider->getStoreUrl(),
            ],
            'product' => [
                'id' => $this->getAttributeValue($product, 'identifier'),
                'name' => $product->getName(),
                'image' => $this->imageHelperFactory->create()->init($product, 'product_base_image')->getUrl(),
                'brand' => $this->getAttributeValue($product, 'brand'),
                'sku' => $this->getAttributeValue($product, 'sku'),
                'variants' => $this->getVariants($product)
            ]
        ];
    }

    /**
     * @return ProductInterface|null
     */
    private function getCurrentProduct(): ?ProductInterface
    {
        if ($this->product === null) {
            $this->product = $this->catalogHelper->getProduct();
        }
        return $this->product;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->configProvider->isEnabled();
    }

    /**
     * @param ProductInterface $product
     * @param string $type
     * @return mixed
     */
    private function getAttributeValue(ProductInterface $product, string $type)
    {
        $attributes = $this->getAttributes();
        $attribute = $attributes[$type] ?? null;
        if ($attribute && $value = $product->getAttributeText($attribute)) {
            return $value;
        }

        return $product->getData($attribute);
    }

    /**
     * @return array
     */
    private function getAttributes(): array
    {
        if (!$this->attributes) {
            $this->attributes = $this->configProvider->getAttributes();
        }
        return $this->attributes;
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    private function getVariants(ProductInterface $product): array
    {
        $variants = [];

        foreach ($this->getAllUsedProducts($product) as $product) {
            $variants[] = [
                'id' => $this->getAttributeValue($product, 'identifier'),
                'size' => $this->getAttributeValue($product, 'size'),
                'color' => $this->getAttributeValue($product, 'color'),
                'available' => $product->isSalable(),
                'sku' => $this->getAttributeValue($product, 'sku'),
                'price' => $product->getPrice(),
                'imageUrl' => $this->imageHelperFactory->create()->init($product, 'image')->getUrl()
            ];
        }
        return $variants;
    }

    /**
     * @param ProductInterface $product
     * @return Collection
     */
    private function getAllUsedProducts(ProductInterface $product): Collection
    {
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());

        return $this->productCollectionFactory->create()
            ->addAttributeToSelect(array_values($this->getAttributes()) + ['image'])
            ->addAttributeToFilter('entity_id', ['in' => $childrenIds])
            ->addPriceData();
    }
}
