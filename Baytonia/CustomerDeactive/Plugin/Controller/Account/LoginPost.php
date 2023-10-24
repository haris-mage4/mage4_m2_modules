<?php

namespace Baytonia\CustomerDeactive\Plugin\Controller\Account;

use Magento\Customer\Model\CustomerFactory;

class LoginPost
{
    protected $customerFactory;

    public function __construct(
        CustomerFactory                                   $customerFactory,
        \Magento\Framework\Message\ManagerInterface       $messageManager,
        \Magento\Customer\Model\Session                   $customerSession,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Controller\ResultFactory       $resultFactory
    )
    {
        $this->customerFactory = $customerFactory;
        $this->messageManager = $messageManager;
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->resultFactory = $resultFactory;
    }

    public function afterExecute(\Magento\Customer\Controller\Account\LoginPost $subject, $result)
    {
        $login = $subject->getRequest()->getPost('login');
        $customer = $this->customerSession->getCustomer();
        $customerId = $customer->getId();

        $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($login['username']);

         if (isset($login['username']) && $customer->getDisable()) {
            $this->customerSession->logout()->setBeforeAuthUrl($this->redirect->getRefererUrl())->setLastCustomerId($customerId);
             $this->messageManager->getMessages(true);
            $message = __('If you want to reactivate you account, Please contact us on this email.');
            $this->messageManager->addNotice($message . ' ( <a href="mailto:it@baytonia.com">  it@baytonia.com  </a> )');
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        } else {
            return $result;
        }
    }
}
