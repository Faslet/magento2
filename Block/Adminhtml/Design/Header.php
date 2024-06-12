<?php declare(strict_types=1);

namespace Faslet\Connect\Block\Adminhtml\Design;

use Faslet\Connect\Api\Config\RepositoryInterface as ConfigRepository;
use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * System Configuration Module information Block
 */
class Header extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Faslet_Connect::system/config/fieldset/header.phtml';

    /**
     * @var ConfigRepository
     */
    private $configRepository;

    /**
     * Header constructor.
     *
     * @param Context $context
     * @param ConfigRepository $config
     */
    public function __construct(
        Context $context,
        ConfigRepository $config
    ) {
        $this->configRepository = $config;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->addClass('faslet');
        return $this->toHtml();
    }

    /**
     * Support link for extension.
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return $this->configRepository->getSupportLink();
    }
}
