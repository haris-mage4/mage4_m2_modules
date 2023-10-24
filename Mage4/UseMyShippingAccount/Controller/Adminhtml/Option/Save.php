<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

use Exception;
use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterface;
use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterfaceFactory;
use InformaticsCommerce\UseMyShippingAccount\Api\DataRepositoryInterface;
use InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\Manager;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use RuntimeException;

class Save extends Option
{
    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var DataRepositoryInterface
     */
    protected $dataRepository;

    /**
     * @var DataInterfaceFactory
     */
    protected $dataFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    public function __construct(Registry $registry, PageFactory $resultPageFactory, ForwardFactory $resultForwardFactory, Context $context, DataRepositoryInterface $dataRepository, DataInterfaceFactory $dataFactory, DataObjectHelper $dataObjectHelper)
    {
        parent::__construct($registry, $resultPageFactory, $resultForwardFactory, $context, $dataRepository, $dataFactory, $dataObjectHelper);
    }

    /**
     * Save action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
//        print_r($data['dynamic_rows']);die;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('option_id');
            if ($id) {
                $model = $this->dataRepository->getById($id);
            } else {
                unset($data['option_id']);
                $model = $this->dataFactory->create();
            }

            try {
                $this->dataObjectHelper->populateWithArray($model, $data, DataInterface::class);
                $model->setData($data);
                $this->dataRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved this option.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['option_id' => $model->getOptionId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['option_id' => $this->getRequest()->getParam('option_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
