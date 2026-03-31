<?php declare(strict_types=1);

namespace Faslet\Connect\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class RmaLifecycleEvent implements OptionSourceInterface
{

    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'created', 'label' => __('RMA Created / Requested')],
            ['value' => 'authorized', 'label' => __('Authorized')],
            ['value' => 'partially_authorized', 'label' => __('Partially Authorized')],
            ['value' => 'return_received', 'label' => __('Return Received')],
            ['value' => 'return_partially_received', 'label' => __('Return Partially Received')],
            ['value' => 'approved', 'label' => __('Approved')],
            ['value' => 'rejected', 'label' => __('Rejected')],
            ['value' => 'processed_and_closed', 'label' => __('Processed and Closed')],
            ['value' => 'closed', 'label' => __('Closed')],
        ];
    }
}