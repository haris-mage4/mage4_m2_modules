<?php

namespace BenefitsMe\Signup\Plugin\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\UrlInterface;

class LoginPost
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var UrlInterface
     */
    protected $url;

    /**
     * @param Session $session
     * @param RedirectFactory $resultRedirectFactory
     * @param UrlInterface $url
     */
    public function __construct(
        Session $session,
        RedirectFactory $resultRedirectFactory,
        UrlInterface $url
    ) {
        $this->session = $session;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->url = $url;
    }

    /**
     * @param \Magento\Customer\Controller\Account\LoginPost $subject
     * @param Redirect $resultRedirect
     * @return Redirect
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $resultRedirect
    ) {
        if ($this->session->isLoggedIn()) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setUrl($this->url->getUrl('/'));
            return $resultRedirect;
        }

        return $resultRedirect;
    }
}
