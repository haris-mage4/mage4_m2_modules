<?php

namespace Mage4\CatalogRestrictions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\CouldNotSaveException;

class RestrictCartQuantity implements ObserverInterface
{
    protected $messageManager;

    public function __construct(
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $qty = $item->getQty();
        $maxAllowedQuantity = 2;

        if ($qty > $maxAllowedQuantity) {

        }
    }
}
