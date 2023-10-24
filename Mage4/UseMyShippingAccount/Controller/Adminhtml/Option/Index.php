<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

use InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

class Index extends Option
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
	    $resultPage->getConfig()->getTitle()->prepend(__("Shipping Options Manager"));
	    return $resultPage;
    }
}
