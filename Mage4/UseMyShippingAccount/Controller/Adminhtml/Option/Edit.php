<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

use InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

class Edit extends Option
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $optionId = $this->getRequest()->getParam('option_id');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('nformaticsCommerce_UseMyShippingAccount::menu')
            ->addBreadcrumb(__('Option'), __('Option'))
            ->addBreadcrumb(__('Manage Option'), __('Manage Option'));

        if ($optionId === null) {
            $resultPage->addBreadcrumb(__('New Option'), __('New Option'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Option'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Option'), __('Edit Option'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->dataRepository->getById($optionId)->getName()
            );
        }
        return $resultPage;
    }
}
