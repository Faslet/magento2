<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Rma;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;
use Faslet\Connect\Api\Log\RepositoryInterface as LogRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class ExportService
{

    private const RMA_MODEL_CLASS = 'Magento\\Rma\\Model\\Rma';

    /**
     * @var ConfigRepositoryInterface
     */
    private $configRepository;
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;
    /**
     * @var ModuleManager
     */
    private $moduleManager;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var PayloadBuilder
     */
    private $payloadBuilder;
    /**
     * @var WebhookPublisher
     */
    private $webhookPublisher;

    public function __construct(
        ConfigRepositoryInterface $configRepository,
        LogRepositoryInterface $logRepository,
        ModuleManager $moduleManager,
        OrderRepositoryInterface $orderRepository,
        PayloadBuilder $payloadBuilder,
        WebhookPublisher $webhookPublisher
    ) {
        $this->configRepository = $configRepository;
        $this->logRepository = $logRepository;
        $this->moduleManager = $moduleManager;
        $this->orderRepository = $orderRepository;
        $this->payloadBuilder = $payloadBuilder;
        $this->webhookPublisher = $webhookPublisher;
    }

    /**
     * Export an Adobe Commerce RMA model to the configured Faslet backend.
     *
     * @param object $rma
     *
     * @return void
     */
    public function export(object $rma): void
    {
        if (!$this->moduleManager->isEnabled('Magento_Rma') || !$this->isRmaEntity($rma)) {
            return;
        }

        $orderId = (int)$this->readValue($rma, ['order_id']);
        if ($orderId <= 0) {
            $this->logRepository->addErrorLog('rma_export_missing_order', $this->extractRawData($rma));
            return;
        }

        try {
            $order = $this->orderRepository->get($orderId);
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('rma_export_order_lookup_failed', [
                'order_id' => $orderId,
                'message' => $exception->getMessage(),
            ]);
            return;
        }

        $storeId = (int)$order->getStoreId();
        if (!$this->configRepository->isEnabled($storeId) || !$this->configRepository->isRmaExportEnabled($storeId)) {
            return;
        }

        if (!$this->configRepository->getRmaEndpointUrl($storeId)) {
            return;
        }

        $eventCode = $this->resolveLifecycleEvent($rma, $storeId);
        if ($eventCode === null) {
            return;
        }

        $payload = $this->payloadBuilder->build($rma, $order, $eventCode);
        $this->logRepository->addDebugLog('rma_export_payload', $payload);
        $this->webhookPublisher->publish($payload, $storeId);
    }

    /**
     * @param object $rma
     *
     * @return bool
     */
    private function isRmaEntity(object $rma): bool
    {
        return is_a($rma, self::RMA_MODEL_CLASS);
    }

    /**
     * @param object $rma
     * @param int $storeId
     *
     * @return string|null
     */
    private function resolveLifecycleEvent(object $rma, int $storeId): ?string
    {
        $status = $this->normalizeStatus((string)$this->readValue($rma, ['status']));
        $previousStatus = $this->normalizeStatus((string)$this->readOrigValue($rma, ['status']));

        if ($this->isCreated($rma) && $this->configRepository->shouldExportRmaLifecycleEvent('created', $storeId)) {
            return 'created';
        }

        if ($status && $status !== $previousStatus && $this->configRepository->shouldExportRmaLifecycleEvent($status, $storeId)) {
            return $status;
        }

        return null;
    }

    /**
     * @param object $rma
     *
     * @return bool
     */
    private function isCreated(object $rma): bool
    {
        if (method_exists($rma, 'isObjectNew') && $rma->isObjectNew()) {
            return true;
        }

        $originalEntityId = $this->readOrigValue($rma, ['entity_id', 'id']);
        $originalIncrementId = $this->readOrigValue($rma, ['increment_id']);

        if ($originalEntityId === null && $originalIncrementId === null) {
            $createdAt = (string)$this->readValue($rma, ['created_at', 'date_requested']);
            $updatedAt = (string)$this->readValue($rma, ['updated_at']);

            if ($createdAt !== '' && $createdAt === $updatedAt) {
                return true;
            }
        }

        return $originalEntityId === null && $originalIncrementId === null;
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