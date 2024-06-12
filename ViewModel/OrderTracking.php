<?php declare(strict_types=1);

namespace Faslet\Connect\ViewModel;

use Exception;
use Faslet\Connect\Api\Config\RepositoryInterface as ConfigProvider;
use Faslet\Connect\Api\Log\RepositoryInterface as LogRepository;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;

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
     * @var OrderFactory
     */
    private $orderFactory;
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

    public function __construct(
        OrderFactory $orderFactory,
        CheckoutSession $checkoutSession,
        ProductRepository $productRepository,
        ConfigProvider $configProvider,
        LogRepository $logRepository
    ) {
        $this->configProvider = $configProvider;
        $this->orderFactory = $orderFactory;
        $this->checkoutSession = $checkoutSession;
        $this->productRepository = $productRepository;
        $this->logRepository = $logRepository;
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
            return $this->orderFactory->create()->load($orderId);
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
            'sku' => $orderItem->getSku(),
            'correlationId' => $orderItem->getProductId(),
            'title' => $orderItem->getName(),
            'variant_id' => $variant ? $variant->getId() : null,
            'variant' => $variant ? $variant->getName() : null,
            'price' => $orderItem->getRowTotalInclTax(),
            'quantity' => (int)$orderItem->getQtyOrdered()
        ];
    }
}
