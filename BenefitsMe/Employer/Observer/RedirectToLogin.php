<?php
namespace BenefitsMe\Employer\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class RedirectToLogin implements ObserverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Constructor.
     *
     * @param Session $customerSession
     * @param RedirectInterface $redirect
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Session $customerSession,
        RedirectInterface $redirect,
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager
    ) {
        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
    }

    /**
     * Redirect unauthenticated users to the login page.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
	return;

        /** @var ActionInterface $controller */
        $controller = $observer->getEvent()->getControllerAction();

        $storeCode = $this->storeManager->getStore()->getCode();
        if ($storeCode == 'default') {
            return;
        }

        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $theURL = explode("/", $urlInterface->getCurrentUrl());

        if(in_array("customer", $theURL) && in_array("account", $theURL) && in_array("login", $theURL)) {
            return;
	}

	if(in_array("getstore", $theURL) && in_array("byemail", $theURL)) {
		return;
	}

	if(count($_POST)) {
		return;
	}


        if (!$this->customerSession->isLoggedIn()) {
            $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
        }
    }
}

