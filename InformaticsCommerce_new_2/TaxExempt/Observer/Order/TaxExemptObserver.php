<?php

namespace InformaticsCommerce\TaxExempt\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session;

class TaxExemptObserver implements ObserverInterface
{
    protected $_session;
    public function __construct(Session $session)
    {
        $this->_session = $session;
    }

    public function execute(Observer $observer)
    {
        $taxExemptNumber = $this->_session->getQuote()->getTaxExemptNumber();
        $fileTax = $this->_session->getQuote()->getUploadDocument();
        $observer->getEvent()->getOrder()->setTaxExemptNumber($taxExemptNumber);
        $observer->getEvent()->getOrder()->setUploadDocument($fileTax);
    }
}
