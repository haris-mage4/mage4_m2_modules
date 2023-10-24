<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

use InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Option
{
    /**
     * Delete the data entity
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $Id = $this->getRequest()->getParam('option_id');
        if ($Id) {
            try {
                $this->dataRepository->deleteById($Id);
                $this->messageManager->addSuccessMessage(__('The option has been deleted.'));
                $resultRedirect->setPath('shippingoptions/option/index');
                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The data no longer exists.'));
                return $resultRedirect->setPath('shippingoptions/option/index');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('shippingoptions/option/index', ['id' => $Id]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem deleting the option'));
                return $resultRedirect->setPath('shippingoptions/option/edit', ['id' => $Id]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find the option to delete.'));
        $resultRedirect->setPath('shippingoptions/option/index');
        return $resultRedirect;
    }
}
