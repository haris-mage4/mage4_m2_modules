<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

class MassStatus extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $bankIds = $this->getRequest()->getParam('bank');
        $status = $this->getRequest()->getParam('status');
        $storeViewId = $this->getRequest()->getParam('store');

        if (!is_array($bankIds) || empty($bankIds)) {
            $this->messageManager->addError(__('Please select bank(s).'));
        } else {
            $bankCollection = $this->_bankCollectionFactory->create()
                ->setStoreViewId($storeViewId)
                ->addFieldToFilter('entity_id', ['in' => $bankIds]);
            try {
                foreach ($bankCollection as $bank) {
                    $bank->setStoreViewId($storeViewId)
                        ->setStatus($status)
                        ->setIsMassupdate(true)
                        ->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been changed status.', count($bankIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/', ['store' => $this->getRequest()->getParam('store')]);
    }
}
