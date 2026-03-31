<?php declare(strict_types=1);

namespace Faslet\Connect\Observer;

use Faslet\Connect\Model\Rma\ExportService;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager as ModuleManager;

class ExportRmaAfterSave implements ObserverInterface
{

    /**
     * @var ExportService
     */
    private $exportService;
    /**
     * @var ModuleManager
     */
    private $moduleManager;

    public function __construct(
        ExportService $exportService,
        ModuleManager $moduleManager
    ) {
        $this->exportService = $exportService;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer): void
    {
        if (!$this->moduleManager->isEnabled('Magento_Rma')) {
            return;
        }

        $object = $observer->getEvent()->getObject();
        if (!is_object($object)) {
            return;
        }

        $this->exportService->export($object);
    }
}