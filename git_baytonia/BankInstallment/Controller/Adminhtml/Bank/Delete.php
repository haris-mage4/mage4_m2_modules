<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

class Delete extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    public function execute()
    {
        $bankId = $this->getRequest()->getParam(static::PARAM_CRUD_ID);
        try {
            $bank = $this->_bankFactory->create()->setId($bankId);
            $bank->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
