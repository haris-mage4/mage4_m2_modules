<?php

namespace Mage4\AdvanceMatrixPricing\Observer\UpdatePrice;

use Mage4\AdvanceMatrixPricing\Helper\Data;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;

class AddToCart implements ObserverInterface
{

    protected $dataHelper;
    protected $coreRegistry;
    protected $logger;
    protected $request;
    protected $checkoutSession;

    public function __construct(Session $checkoutSession, LoggerInterface $logger, Data $dataHelper, Registry $coreRegistry, RequestInterface $request)
    {
        $this->dataHelper = $dataHelper;
        $this->coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->request = $request;
        $this->checkoutSession = $checkoutSession;
    }


    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getData('quote_item');
        $productType = $item->getProduct()->getTypeId();
        $selectedPrice = $this->request->getParam('matrix_price');
        $matrixPrice = ($selectedPrice) ? (float) $selectedPrice : 0;

        if ($productType == Type::TYPE_SIMPLE) {
            $setCustomPrice = $item->getPrice() + $matrixPrice;
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $item->setCustomPrice($setCustomPrice);
            $item->setOriginalCustomPrice($setCustomPrice);
            $item->getProduct()->setIsSuperMode(true);
            $this->checkoutSession->setPriceMatrix($item->getCustomPrice());
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            /** @var \Psr\Log\LoggerInterface $logger */
            $logger = $objectManager->get('\Psr\Log\LoggerInterface');
            $logger->error('custom work addto cart => '.json_encode($this->checkoutSession->getPriceMatrix()));
        }
        return $this;
    }
}

