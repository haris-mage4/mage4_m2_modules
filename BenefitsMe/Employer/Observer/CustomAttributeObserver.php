<?php

namespace BenefitsMe\Employer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;

class CustomAttributeObserver implements ObserverInterface
{
    public function __construct(
        protected RequestInterface $_request,
        protected LoggerInterface $_logger
    ) { }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $order = $observer->getEvent()->getOrder();

        $terms = $_COOKIE['terms'];

        $customAttributes = [
            'payroll_deduction' => $terms,
        ];

        foreach ($customAttributes as $attributeCode => $attributeValue) {
            $order->setData($attributeCode, $attributeValue);
        }

        $order->save();
    }
}