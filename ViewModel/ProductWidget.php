<?php declare(strict_types=1);

namespace Faslet\Connect\ViewModel;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigProvider;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

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
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * ProductWidget constructor.
     * @param ProductCollectionFactory $productCollectionFactory
     * @param ImageFactory $imageHelperFactory
     * @param ConfigProvider $configProvider
     * @param CatalogHelper $catalogHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        ImageFactory $imageHelperFactory,
        ConfigProvider $configProvider,
        CatalogHelper $catalogHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->configProvider = $configProvider;
        $this->catalogHelper = $catalogHelper;
        $this->storeManager = $storeManager;
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
                'image' => $this->getImageUrl($product),
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

        foreach ($this->getAllUsedProducts($product) as $childProduct) {
            $variants[] = [
                'id' => $this->getAttributeValue($childProduct, 'identifier'),
                'size' => $this->getAttributeValue($childProduct, 'size'),
                'color' => $this->getAttributeValue($childProduct, 'color'),
                'available' => $childProduct->isSalable(),
                'sku' => $this->getAttributeValue($childProduct, 'sku'),
                'price' => $childProduct->getPrice(),
                'imageUrl' => $this->getImageUrl($childProduct),
            ];
        }
        return $variants;
    }

    /**
     * @param Product $product
     * @return string|null
     */
    private function getImageUrl(Product $product): ?string
    {
        $imageUrl = null;
        if ($product->getImage()) {
            try {
                $imageUrl = $this->storeManager->getStore()
                        ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                    . 'catalog/product' . $product->getImage();
            } catch (\Exception $e) {
                return null;
            }
        }
        return $imageUrl;
    }

    /**
     * @param ProductInterface $product
     * @return Collection
     */
    private function getAllUsedProducts(ProductInterface $product): Collection
    {
        $childrenIds = $product->getTypeInstance()->getChildrenIds($product->getId());

        return $this->productCollectionFactory->create()
            ->addAttributeToSelect(array_merge(array_values($this->getAttributes()), ['image']))
            ->addAttributeToFilter('entity_id', ['in' => $childrenIds])
            ->addPriceData();
    }
}
