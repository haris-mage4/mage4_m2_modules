<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

class Edit extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('entity_id');
        $storeViewId = $this->getRequest()->getParam('store');
        $model = $this->_bankFactory->create();

        if ($id) {
            $model->setStoreViewId($storeViewId)->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This bank no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }
        
        $data = $this->_getSession()->getFormData(true);
        
        if (!empty($data)) {
            $model->setData($data);
        }else if(!$id){
            $bank_name = $this->_getSession()->getBankName();
            $url = $this->_getSession()->getUrl();
            if(isset($bank_name) && strlen($bank_name)){
                $data = [ 'name' => $bank_name, 'url' => $url ];
                $model->setData($data);
            }
            
        }

        $this->_coreRegistry->register('bank', $model);

        $resultPage = $this->_resultPageFactory->create();

        return $resultPage;
    }
}
