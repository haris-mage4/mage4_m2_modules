<?php

namespace Mage4\Appointment\Controller\Adminhtml\Data;

use Mage4\Appointment\Controller\Adminhtml\Data;

class Index extends Data
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
	    $resultPage->getConfig()->getTitle()->prepend(__("Appointment Form"));
	    return $resultPage;
    }
}
