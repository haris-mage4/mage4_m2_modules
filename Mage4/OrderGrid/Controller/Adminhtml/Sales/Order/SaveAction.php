<?php

namespace Mage4\OrderGrid\Controller\Adminhtml\Sales\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\ResourceModel\Order as OrderResourceModel;

class SaveAction extends Action
{
    private OrderRepositoryInterface $orderRepo;
    private OrderResourceModel $orderResModel;

    public function __construct(Context $context, OrderRepositoryInterface $orderRepo, OrderResourceModel $orderResModel)
    {
        $this->orderRepo = $orderRepo;
        $this->orderResModel = $orderResModel;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getPostValue(); 
        try {
            if ($data) {
                $order = $this->orderRepo->get($data['order_id']);
                $order->setData('merchant_account', $data['merchant_account']);
                $this->orderResModel->save($order);
                $this->messageManager->addSuccessMessage(__('Merchant Account Name is saved successfully!!'));
            }
        } catch (\Throwable $e) {
            $this->messageManager->addExceptionMessage(
                $e, __('Something went wrong while saving Merchant Account.')
            );
        }

        return $this->_redirect('sales/order/index');
    }
}
