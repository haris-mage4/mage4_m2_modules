<?php

namespace Baytonia\CustomerDeactive\Controller\Deactive;

//use Magento\Customer\Controller\AbstractAccount;
//use Magento\Framework\App\Action\Action;
//use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\Exception\LocalizedException;
use  Baytonia\CustomerDeactive\Block\Customer\Account\DeactiveLink;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\SessionCleanerInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;

class FormPost extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    protected $blockLink;
    protected $customerFactory;
    protected $resultPageFactory;
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var SessionCleanerInterface
     */
    private $sessionCleaner;
    private $_jsonFactory;

    public function __construct(JsonFactory $_jsonFactory, Session $customerSession, SessionCleanerInterface $sessionCleaner = null, CustomerFactory $customerFactory, DeactiveLink $blockLink, \Magento\Framework\View\Result\PageFactory $resultPageFactory, Context $context)
    {
        $this->_jsonFactory = $_jsonFactory;
        $this->session = $customerSession;
        $objectManager = ObjectManager::getInstance();
        $this->sessionCleaner = $sessionCleaner ?? $objectManager->get(SessionCleanerInterface::class);
        $this->customerFactory = $customerFactory;
        $this->blockLink = $blockLink;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = [];
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        try {
            $email = $this->blockLink->getCustomerEmail();
            $customer = $this->customerFactory->create()->setWebsiteId(1)->loadByEmail($email);
            if (!empty($customer->getData('email')) && !$customer->getDisable()) {
                $customer->setDisable(1);
                $customer->save();
                $this->getLogoutCustomer();
                $this->messageManager->addSuccessMessage(    __('Account has beed deactivated, you can active again.'));
                $result = [
                    "success" => true,
                    "message"  =>
                        __('Account has beed deactivated, you can active again.')
                ];
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        $this->resultRedirectFactory->create()->setPath('customer/account');
        return $this->_jsonFactory->create()->setData($result);
    }

    /**
     * Retrieve cookie manager
     *
     * @return PhpCookieManager
     * @deprecated 100.1.0
     */
    private function getCookieManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return CookieMetadataFactory
     * @deprecated 100.1.0
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }
        return $this->cookieMetadataFactory;
    }

    public function getLogoutCustomer()
    {
        $lastCustomerId = $this->session->getId();
        $this->session->logout()->setBeforeAuthUrl($this->_redirect->getRefererUrl())
            ->setLastCustomerId($lastCustomerId);
        $this->sessionCleaner->clearFor((int)$lastCustomerId);
        if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
            $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
            $metadata->setPath('/');
            $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
        }
    }
}
