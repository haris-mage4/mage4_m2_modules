<?php

namespace InformaticsCommerce\TaxExempt\Block\Account\Dashboard\Address;

use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class TaxExemptBlock extends Template
{
    protected $_currentCustomer;

    public function __construct(CurrentCustomer $currentCustomer, Context $context, array $data = [])
    {
        $this->_currentCustomer = $currentCustomer;
        parent::__construct($context, $data);
    }

    public function getCustomerTaxExempt()
    {
        $attrValue = ($this->getCustomer()->getCustomAttribute('tax_exempt_number'))
            ?
            $this->getCustomer()->getCustomAttribute('tax_exempt_number')->getValue()
            : null;
        return $attrValue;
    }

    public function getCustomer()
    {
        try {
            return $this->_currentCustomer->getCustomer();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function isCustomerTaxExempt()
    {
        $isCustomerTaxExempt = ($this->getCustomer()->getGroupId() == 4)
            ?
            true
            :
            false;
        return $isCustomerTaxExempt;
    }

}
