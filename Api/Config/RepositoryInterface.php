<?php declare(strict_types=1);

namespace Faslet\Connect\Api\Config;

/**
 * Config repository interface
 */
interface RepositoryInterface extends System\DataInterface
{

    public const EXTENSION_CODE = 'Faslet_Connect';
    public const XML_PATH_EXTENSION_VERSION = 'faslet_connect/general/version';
    public const XML_PATH_EXTENSION_ENABLE = 'faslet_connect/general/enable';
    public const XML_PATH_DEBUG = 'faslet_connect/general/debug';
    public const XML_PATH_RMA_EXPORT_ENABLE = 'faslet_connect/rma/export_enabled';
    public const XML_PATH_RMA_ENDPOINT_URL = 'faslet_connect/rma/endpoint_url';
    public const XML_PATH_RMA_AUTH_TOKEN = 'faslet_connect/rma/auth_token';
    public const XML_PATH_RMA_TIMEOUT = 'faslet_connect/rma/timeout';
    public const XML_PATH_RMA_LIFECYCLE_EVENTS = 'faslet_connect/rma/lifecycle_events';
    public const DEFAULT_RMA_TIMEOUT = 5;
    public const MODULE_SUPPORT_LINK = 'https://faslet.me/faslet/contact';

    /**
     * Check if module is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled(?int $storeId = null): bool;

    /**
     * Get extension version
     *
     * @return string
     */
    public function getExtensionVersion(): string;

    /**
     * Get extension code
     *
     * @return string
     */
    public function getExtensionCode(): string;

    /**
     * Check if debug mode is enabled
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebugMode(?int $storeId = null): bool;

    /**
     * Support link for extension
     *
     * @return string
     */
    public function getSupportLink(): string;

    /**
     * Returns store url of current store
     *
     * @return string
     */
    public function getStoreUrl(): string;

    /**
     * Check if Commerce RMA export is enabled.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isRmaExportEnabled(?int $storeId = null): bool;

    /**
     * Get the Faslet RMA ingestion endpoint.
     *
     * @param int|null $storeId
     *
     * @return string|null
     */
    public function getRmaEndpointUrl(?int $storeId = null): ?string;

    /**
     * Get the Faslet auth token used for RMA exports.
     *
     * @param int|null $storeId
     *
     * @return string|null
     */
    public function getRmaAuthToken(?int $storeId = null): ?string;

    /**
     * Get the outbound request timeout for RMA exports.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function getRmaTimeout(?int $storeId = null): int;

    /**
     * Get the configured RMA lifecycle events that should be exported.
     *
     * @param int|null $storeId
     *
     * @return array
     */
    public function getRmaLifecycleEvents(?int $storeId = null): array;

    /**
     * Check if a specific lifecycle event should be exported.
     *
     * @param string $eventCode
     * @param int|null $storeId
     *
     * @return bool
     */
    public function shouldExportRmaLifecycleEvent(string $eventCode, ?int $storeId = null): bool;
}
