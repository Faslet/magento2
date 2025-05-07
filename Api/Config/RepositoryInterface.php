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
    public const MODULE_SUPPORT_LINK = 'https://site.faslet.me/contact-us';

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
}
