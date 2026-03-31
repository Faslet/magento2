<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Rma;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;

class PayloadBuilder
{

    /**
     * @var ConfigRepositoryInterface
     */
    private $configRepository;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    public function __construct(
        ConfigRepositoryInterface $configRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->configRepository = $configRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Build a backend ingestion payload for an Adobe Commerce RMA export.
     *
     * @param object $rma
     * @param OrderInterface $order
     * @param string $eventCode
     *
     * @return array
     */
    public function build(object $rma, OrderInterface $order, string $eventCode): array
    {
        $storeId = (int)$order->getStoreId();
        $status = $this->normalizeStatus((string)$this->readValue($rma, ['status']));
        $previousStatus = $this->normalizeStatus((string)$this->readOrigValue($rma, ['status']));

        return [
            'event' => $eventCode,
            'event_id' => $this->buildEventId($rma, $eventCode),
            'occurred_at' => $this->readValue($rma, ['updated_at', 'date_requested', 'created_at']),
            'shop_id' => $this->configRepository->getShopId($storeId),
            'store' => [
                'id' => $storeId,
                'code' => $order->getStoreName(),
            ],
            'order' => [
                'entity_id' => (int)$order->getEntityId(),
                'increment_id' => $order->getIncrementId(),
                'customer_email' => $order->getCustomerEmail(),
            ],
            'rma' => [
                'entity_id' => $this->readValue($rma, ['entity_id', 'id']),
                'increment_id' => $this->readValue($rma, ['increment_id']),
                'status' => $status,
                'previous_status' => $previousStatus,
                'created_at' => $this->readValue($rma, ['created_at', 'date_requested']),
                'updated_at' => $this->readValue($rma, ['updated_at']),
                'comments' => $this->extractComments($rma),
                'attributes' => $this->sanitizeData($this->extractRawData($rma)),
            ],
            'items' => $this->buildItems($rma, $order),
        ];
    }

    /**
     * @param object $rma
     * @param OrderInterface $order
     *
     * @return array
     */
    private function buildItems(object $rma, OrderInterface $order): array
    {
        $items = [];
        $storeId = (int)$order->getStoreId();
        $orderItems = $this->indexOrderItems($order);

        foreach ($this->extractItems($rma) as $rmaItem) {
            $orderItemId = (int)$this->readValue($rmaItem, ['order_item_id']);
            $orderItem = $orderItems[$orderItemId] ?? null;
            $productData = $this->extractProductData($orderItem, $storeId);

            $items[] = [
                'entity_id' => $this->readValue($rmaItem, ['entity_id', 'id']),
                'order_item_id' => $orderItemId ?: null,
                'product_id' => $this->readValue($rmaItem, ['product_id']) ?: ($orderItem ? (int)$orderItem->getProductId() : null),
                'sku' => $this->readValue($rmaItem, ['product_sku', 'sku']) ?: ($orderItem ? $orderItem->getSku() : null),
                'title' => $this->readValue($rmaItem, ['product_name', 'name']) ?: ($orderItem ? $orderItem->getName() : null),
                'correlation_id' => $productData['correlation_id'],
                'variant_id' => $productData['variant_id'],
                'requested_qty' => $this->readValue($rmaItem, ['qty_requested', 'qty']),
                'authorized_qty' => $this->readValue($rmaItem, ['qty_authorized']),
                'returned_qty' => $this->readValue($rmaItem, ['qty_returned']),
                'reason' => $this->readValue($rmaItem, ['reason']),
                'reason_label' => $this->readValue($rmaItem, ['reason_label', 'reason']),
                'condition' => $this->readValue($rmaItem, ['condition']),
                'condition_label' => $this->readValue($rmaItem, ['condition_label', 'condition']),
                'resolution' => $this->readValue($rmaItem, ['resolution']),
                'resolution_label' => $this->readValue($rmaItem, ['resolution_label', 'resolution']),
                'attributes' => $this->sanitizeData($this->extractRawData($rmaItem)),
            ];
        }

        return $items;
    }

    /**
     * @param OrderInterface $order
     *
     * @return array
     */
    private function indexOrderItems(OrderInterface $order): array
    {
        $items = [];

        foreach ($order->getItems() as $orderItem) {
            $items[(int)$orderItem->getItemId()] = $orderItem;
        }

        return $items;
    }

    /**
     * @param OrderItemInterface|null $orderItem
     * @param int $storeId
     *
     * @return array
     */
    private function extractProductData(?OrderItemInterface $orderItem, int $storeId): array
    {
        if ($orderItem === null) {
            return [
                'correlation_id' => null,
                'variant_id' => null,
            ];
        }

        $product = $orderItem->getProduct();
        if ($product === null && $orderItem->getProductId()) {
            try {
                $product = $this->productRepository->getById((int)$orderItem->getProductId(), false, $storeId);
            } catch (\Exception $exception) {
                $product = null;
            }
        }

        if ($product === null) {
            return [
                'correlation_id' => null,
                'variant_id' => null,
            ];
        }

        $attributes = $this->configRepository->getAttributes($storeId);
        $identifierAttribute = $attributes['identifier'] ?? null;

        return [
            'correlation_id' => $identifierAttribute ? $product->getData($identifierAttribute) : null,
            'variant_id' => $identifierAttribute ? $product->getData($identifierAttribute) : null,
        ];
    }

    /**
     * @param object $rma
     *
     * @return iterable
     */
    private function extractItems(object $rma): iterable
    {
        foreach (['getItems', 'getItemsCollection'] as $method) {
            if (method_exists($rma, $method)) {
                $items = $rma->{$method}();
                if (is_array($items) || $items instanceof \Traversable) {
                    return $items;
                }
            }
        }

        $items = $this->readValue($rma, ['items']);
        if (is_array($items) || $items instanceof \Traversable) {
            return $items;
        }

        return [];
    }

    /**
     * @param object $rma
     *
     * @return array
     */
    private function extractComments(object $rma): array
    {
        $result = [];

        foreach (['getComments', 'getCommentsCollection'] as $method) {
            if (!method_exists($rma, $method)) {
                continue;
            }

            $comments = $rma->{$method}();
            if (!is_array($comments) && !$comments instanceof \Traversable) {
                continue;
            }

            foreach ($comments as $comment) {
                $result[] = [
                    'entity_id' => $this->readValue($comment, ['entity_id', 'id']),
                    'comment' => $this->readValue($comment, ['comment', 'comment_text']),
                    'created_at' => $this->readValue($comment, ['created_at']),
                    'status' => $this->readValue($comment, ['status']),
                ];
            }

            if ($result) {
                return $result;
            }
        }

        return $result;
    }

    /**
     * @param object $subject
     * @param array $keys
     *
     * @return mixed|null
     */
    private function readValue(object $subject, array $keys)
    {
        foreach ($keys as $key) {
            $camelizedKey = str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            $getter = 'get' . $camelizedKey;

            if (method_exists($subject, $getter)) {
                $value = $subject->{$getter}();
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }

            if ($subject instanceof DataObject) {
                $value = $subject->getData($key);
                if ($value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * @param object $subject
     * @param array $keys
     *
     * @return mixed|null
     */
    private function readOrigValue(object $subject, array $keys)
    {
        if (!method_exists($subject, 'getOrigData')) {
            return null;
        }

        foreach ($keys as $key) {
            $value = $subject->getOrigData($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param object $subject
     *
     * @return array
     */
    private function extractRawData(object $subject): array
    {
        if ($subject instanceof DataObject) {
            return $subject->getData();
        }

        if (method_exists($subject, 'getData')) {
            $data = $subject->getData();
            if (is_array($data)) {
                return $data;
            }
        }

        return [];
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function sanitizeData(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_object($value)) {
                unset($data[$key]);
                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->sanitizeData($value);
            }
        }

        return $data;
    }

    /**
     * @param object $rma
     * @param string $eventCode
     *
     * @return string
     */
    private function buildEventId(object $rma, string $eventCode): string
    {
        $rmaId = (string)$this->readValue($rma, ['increment_id', 'entity_id', 'id']);

        return sprintf('faslet_rma:%s:%s', $rmaId, $eventCode);
    }

    /**
     * @param string $status
     *
     * @return string|null
     */
    private function normalizeStatus(string $status): ?string
    {
        $status = trim($status);
        if ($status === '') {
            return null;
        }

        $normalized = strtolower((string)preg_replace('/[^a-z0-9]+/i', '_', $status));

        return trim($normalized, '_');
    }
}