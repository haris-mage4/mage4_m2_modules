<?php

namespace Baytonia\CustomerDeactive\Block\Customer\Account;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

class DeactiveLink extends Template
{
    private $customerSession;

    public function __construct(Session $customerSession, Template\Context $context, array $data = [])
    {
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    public function getCustomerId(){
        $id = $this->customerSession->getCustomerId();
        return $id;
    }

    public function isLoggedIn(){
        if ($this->customerSession->isLoggedIn()){
            return true;
        }else{
            return false;
        }
    }

    public function getCustomerEmail(){
        return $this->customerSession->getCustomer()->getEmail();
    }
}
