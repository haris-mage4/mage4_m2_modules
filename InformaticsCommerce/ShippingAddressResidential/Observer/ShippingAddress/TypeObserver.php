<?php

namespace InformaticsCommerce\ShippingAddressResidential\Observer\ShippingAddress;
use Magento\Quote\Api\Data\AddressExtension;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\QuoteRepository;

class TypeObserver implements \Magento\Framework\Event\ObserverInterface
{
    protected $_session;
    protected $quoteRepository;

    public function __construct(Session $session, QuoteRepository $quoteRepository)
    {
        $this->_session = $session;
        $this->_quoteRepository = $quoteRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote_id = $observer->getEvent()->getOrder()->getQuoteId();
        $quote = $this->_quoteRepository->get($quote_id);
        $order->setAddressType($quote->getAddressType());
        $order->setAddressType($quote->getAddressType());
    }
}
