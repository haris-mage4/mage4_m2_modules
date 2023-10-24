<?php

namespace Baytonia\CustomerDeactive\Controller\Deactive;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;


class Index extends Action implements HttpGetActionInterface
{
    private $customerSession;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function __construct(Session $customerSession, PageFactory $resultPageFactory, Context $context)
    {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()){
            return $this->resultRedirectFactory->create()->setPath('customer/account');
        }
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        return $resultPage;
    }
}
