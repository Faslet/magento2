<?php declare(strict_types=1);

namespace Faslet\Connect\Block\Adminhtml\System\Config\Button;

use Exception;
use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Faslet\Connect\Api\Log\RepositoryInterface as LogRepository;

/**
 * Log check button class
 */
class Log extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Faslet_Connect::system/config/button/log.phtml';

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element): string
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @param string $type
     * @return string
     */
    public function getDownloadUrl(string $type): string
    {
        return $this->getUrl('faslet/log/stream', ['type' => $type]);
    }

    /**
     * @param string $type
     * @return string
     */
    public function getButtonHtml(string $type): string
    {
        try {
            return $this->getLayout()
                ->createBlock(Button::class)
                ->setData([
                    'id' => 'mm-ui-button_' . $type,
                    'label' => __('Show last %1 %2 log records', LogRepository::STREAM_DEFAULT_LIMIT, $type)
                ])->toHtml();
        } catch (Exception $e) {
            return '';
        }
    }
}
