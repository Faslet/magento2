<?php declare(strict_types=1);

namespace Faslet\Connect\ViewModel;

use Exception;
use Faslet\Connect\Api\Config\RepositoryInterface as ConfigProvider;
use Faslet\Connect\Api\Log\RepositoryInterface as LogRepository;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * OrderTracking view model
 */
class OrderTracking implements ArgumentInterface
{

    /**
     * @var ConfigProvider
     */
    private $configProvider;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;
    /**
     * @var ProductRepository
     */
    private $productRepository;
    /**
     * @var LogRepository
     */
    private $logRepository;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var array
     */
    private $attributes = [];

    public function __construct(
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository,
        ConfigProvider $configProvider,
        LogRepository $logRepository,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->logRepository = $logRepository;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @return array[]
     */
    public function getOrderTrackingData(): ?array
    {
        if (!$this->configProvider->isEnabled() || !$this->getShopId() || !$order = $this->getOrder()) {
            return null;
        }

        $orderData = [
            'order' => $order->getIncrementId(),
            'payment_status' => $order->getBaseTotalDue() == 0 ? 'paid' : 'unpaid',
        ];

        $orderTrackingData = [];
        foreach ($order->getAllVisibleItems() as $orderItem) {
            if ($orderItem->getQtyOrdered() == 0 || $orderItem->getParentItemId() !== null) {
                continue;
            }
            $orderTrackingData[] = $orderData + $this->getItemData($orderItem);
        }

        $this->logRepository->addDebugLog(
            'getOrderTrackingData order: #' . $order->getIncrementId(),
            $orderTrackingData
        );

        return $orderTrackingData;
    }

    /**
     * @return string|null
     */
    public function getShopId(): ?string
    {
        return $this->configProvider->getShopId();
    }

    /**
     * @return Order|null
     */
    public function getOrder(): ?Order
    {
        if ($orderId = $this->checkoutSession->getLastOrderId()) {
            try {
                return $this->orderRepository->get($orderId);
            } catch (NoSuchEntityException $e) {
                $this->logRepository->addErrorLog('getOrder Order ID not found', $e->getMessage());
            }
        }

        return null;
    }

    /**
     * @param Order\Item $orderItem
     * @return array
     */
    private function getItemData(Order\Item $orderItem): array
    {
        try {
            $variant = $this->productRepository->get($orderItem->getSku());
        } catch (Exception $exception) {
            $this->logRepository->addErrorLog(
                'getItemData Order: ' . $orderItem->getOrderId(),
                $exception->getMessage()
            );
            $variant = null;
        }

        return [
            'sku' => $variant ? $this->getAttributeValue($variant, 'sku') : $this->getAttributeValue($orderItem->getProduct(), 'sku'),
            'correlationId' => $this->getAttributeValue($orderItem->getProduct(), 'identifier'),
            'title' => $orderItem->getName(),
            'variant_id' => $variant ? $this->getAttributeValue($variant, 'identifier') : null,
            'variant' => $variant ? $variant->getName() : null,
            'price' => $orderItem->getRowTotalInclTax(),
            'quantity' => (int)$orderItem->getQtyOrdered()
        ];
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
            $storeId = (int)$this->storeManager->getStore()->getId();
            $this->attributes = $this->configProvider->getAttributes($storeId);
        }
        return $this->attributes;
    }
}
