<?php

namespace Baytonia\CustomerDeactive\Plugin\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\CustomerFactory;

class CreatePost
{
    protected $accountManagement;
    protected $customerFactory;
    protected $sessionFactory;

    public function __construct(
        AccountManagementInterface                        $accountManagement,
        CustomerFactory                                   $customerFactory,
        \Magento\Framework\Message\ManagerInterface       $messageManager,
        \Magento\Customer\Model\SessionFactory            $sessionFactory,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\ResultFactory       $resultFactory
    )
    {
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->messageManager = $messageManager;
        $this->sessionFactory = $sessionFactory;
        $this->redirect = $redirect;
        $this->resultFactory = $resultFactory;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\CreatePost $subject, $result)
    {
        $create = $subject->getRequest()->getParams();
        $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($create['email']);
        if ($customer && $customer->getDisable() ) {
            $this->messageManager->getMessages(true);
            $message = __('If you want to reactivate you account, Please contact us on this email.');
         $this->messageManager->addNotice($message . ' ( <a href="mailto:it@baytonia.com">  it@baytonia.com  </a> )');
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('customer/account/create');
        }else{
            return $result;
        }
    }
}
