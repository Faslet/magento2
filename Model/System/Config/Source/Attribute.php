<?php declare(strict_types=1);

namespace Faslet\Connect\Model\System\Config\Source;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Attribute implements OptionSourceInterface
{

    /**
     * Options array
     *
     * @var array
     */
    public $options = null;
    /**
     * @var Repository
     */
    private $attributeRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Attributes constructor.
     *
     * @param Repository $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Repository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        if (!$this->options) {
            $options[] = ['value' => '', 'label' => __('None / Do not use')];
            $options[] = $this->getAttributesArray();
            $this->options = $options;
        }

        return $this->options;
    }

    /**
     * @return array
     */
    public function getAttributesArray(): array
    {
        $attributes = [];
        $attributes[] = ['value' => 'entity_id', 'label' => 'Product Id'];

        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var AbstractAttribute $attribute */
        foreach ($this->attributeRepository->getList($searchCriteria)->getItems() as $attribute) {
            if (!$attribute->getIsVisible() || !$attribute->getFrontendLabel()) {
                continue;
            }
            $attributes[] = [
                'value' => $attribute->getAttributeCode(),
                'label' => str_replace("'", '', (string)$attribute->getFrontendLabel())
            ];
        }

        usort($attributes, function ($a, $b) {
            return strcmp($a["label"], $b["label"]);
        });

        return [
            'label' => __('Attributes'),
            'value' => $attributes,
            'optgroup-name' => __('Attributes')
        ];
    }
}
