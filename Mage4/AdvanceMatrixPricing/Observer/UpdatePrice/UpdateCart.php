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
use Magento\Checkout\Model\Cart;

class UpdateCart implements ObserverInterface
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $objectManager->get(\Psr\Log\LoggerInterface::class);
        $logger->info('custom work 111 ');

        $item = $observer->getEvent()->getData('item');
        $productType = $item->getProduct()->getTypeId();
        $selectedPrice = $this->request->getParam('matrix_price');
        $matrixPrice = ($selectedPrice) ? (float)$selectedPrice : 0;


        if ($productType == Type::TYPE_SIMPLE) {
            $setCustomPrice = $matrixPrice;
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $item->setCustomPrice($setCustomPrice);
            $item->setOriginalCustomPrice($setCustomPrice);
            $item->getProduct()->setIsSuperMode(true);

            $item->save();

            /** @var \Magento\Checkout\Model\Cart $cart */
            $cart = $objectManager->get(\Magento\Checkout\Model\Cart::class);

            /** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepo */
            $quoteRepo = $objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);

            $quote = $cart->getQuote();
            $quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->save();

            $logger->info('custom work 222 '.$item->getId());
        }
        return $this;
    }
}

