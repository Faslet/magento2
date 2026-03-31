<?php declare(strict_types=1);

namespace Faslet\Connect\Model\Rma;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigRepositoryInterface;
use Faslet\Connect\Api\Log\RepositoryInterface as LogRepositoryInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;

class WebhookPublisher
{

    /**
     * @var ConfigRepositoryInterface
     */
    private $configRepository;
    /**
     * @var CurlFactory
     */
    private $curlFactory;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var LogRepositoryInterface
     */
    private $logRepository;

    public function __construct(
        ConfigRepositoryInterface $configRepository,
        CurlFactory $curlFactory,
        Json $json,
        LogRepositoryInterface $logRepository
    ) {
        $this->configRepository = $configRepository;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->logRepository = $logRepository;
    }

    /**
     * Publish the given payload to the configured Faslet endpoint.
     *
     * @param array $payload
     * @param int $storeId
     *
     * @return void
     */
    public function publish(array $payload, int $storeId): void
    {
        $url = $this->configRepository->getRmaEndpointUrl($storeId);
        if (!$url) {
            return;
        }

        $requestBody = $this->json->serialize($payload);
        $eventId = (string)($payload['event_id'] ?? 'unknown');

        try {
            $curl = $this->curlFactory->create();
            $timeout = $this->configRepository->getRmaTimeout($storeId);
            $curl->setOption(CURLOPT_CONNECTTIMEOUT, $timeout);
            $curl->setOption(CURLOPT_TIMEOUT, $timeout);
            $curl->addHeader('Accept', 'application/json');
            $curl->addHeader('Content-Type', 'application/json');
            $curl->addHeader('X-Faslet-Event-Id', $eventId);

            if ($token = $this->configRepository->getRmaAuthToken($storeId)) {
                $curl->addHeader('Authorization', 'Bearer ' . $token);
            }

            $curl->post($url, $requestBody);

            $logPayload = [
                'url' => $url,
                'store_id' => $storeId,
                'status' => $curl->getStatus(),
                'body' => $curl->getBody(),
                'event_id' => $eventId,
            ];

            if ($curl->getStatus() >= 400) {
                $this->logRepository->addErrorLog('rma_export_response', $logPayload);
                return;
            }

            $this->logRepository->addDebugLog('rma_export_response', $logPayload);
        } catch (\Exception $exception) {
            $this->logRepository->addErrorLog('rma_export_exception', [
                'store_id' => $storeId,
                'event_id' => $eventId,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}