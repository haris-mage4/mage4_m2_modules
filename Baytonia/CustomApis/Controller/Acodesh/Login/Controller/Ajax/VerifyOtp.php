<?php

namespace Baytonia\CustomApis\Controller\Acodesh\Login\Controller\Ajax;

use Acodesh\Login\Model\Account as AccountModel;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\CustomerFactory as createCustomer;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Magento\Framework\UrlInterface;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Store\Model\StoreManagerInterface;

class VerifyOtp extends \Acodesh\Login\Controller\Ajax\VerifyOtp
{
    protected $quoteManagement;

    public function __construct(QuoteManagement $quoteManagement, Context $context, Data $helper, JsonFactory $resultJsonFactory, AccountModel $accountModel, CustomerRepositoryInterface $customerRepository, CustomerSession $session, \Magento\Quote\Model\QuoteFactory $quoteFactory, \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory, Address $addressHelper, RawFactory $resultRawFactory, Registration $customerRegistration, Validator $formKeyValidator, AccountManagementInterface $accountManagement, CustomerExtractor $customerExtractor, SubscriberFactory $subscriberFactory, Url $customerUrl, UrlInterface $urlModel, StoreManagerInterface $storeManager, Redirect $accountRedirect, ScopeConfigInterface $scopeConfig, Escaper $escaper, CookieMetadataFactory $cookieMetadataFactory, PhpCookieManager $cookieMetadataManager, \Magento\Framework\App\ResourceConnection $resourceConnection, \Webkul\MobikulCore\Helper\Data $mobihelper, \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory, createCustomer $createCustomer)
    {
        $this->quoteManagement = $quoteManagement;
        parent::__construct($context, $helper, $resultJsonFactory, $accountModel, $customerRepository, $session, $quoteFactory, $tokenModelFactory, $addressHelper, $resultRawFactory, $customerRegistration, $formKeyValidator, $accountManagement, $customerExtractor, $subscriberFactory, $customerUrl, $urlModel, $storeManager, $accountRedirect, $scopeConfig, $escaper, $cookieMetadataFactory, $cookieMetadataManager, $resourceConnection, $mobihelper, $resultRedirectFactory, $createCustomer);
    }

    public function execute()
    {
        $requestData = $this->getRequest()->getParams();
        $message = null;
        $resultJson = $this->resultJsonFactory->create();
        $customerModel = $this->createCustomer->create();
        $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        $storeId = $this->storeManager->getStore()->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

        if (!$requestData) {
            $response['errors'] = true;
            $response['message'] = __("An error occurred, please try again later.");
            return $resultJson->setData($response);
        }
        try {
            if (isset($requestData['otpId'])) {
                $otpRow = $this->accountModel->getOtpRow($requestData['otpId']);
                $customer = $customerModel->setWebsiteId($websiteId)->load($otpRow->getCustomerId());
                $customerId = $customer->getId();
                  if ($customer->getDisable()){
                    $response = [
                        'error' => false,
                        'message' => __('If you want to reactivate you account, Please contact us on this email. '
                            . ' ( <a href="mailto:it@baytonia.com">  it@baytonia.com  </a> )'
                        )
                    ];
                    return $resultJson->setData($response);
                }
            } else {
                $customerId = 0;
            }

            if ($requestData['type'] == 'login') {

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                try {
                    $sql = "DELETE FROM `quote_address` WHERE  `quote_id`= " . $requestData['quoteId'] . "";
                    $result111 = $connection->fetchAll($sql);
                } catch (\Throwable $th) {
                }

                $this->accountModel->verifyUserOtp($customerId, $requestData['otp'], $requestData['type']);
                $customerInterface = $this->customerRepository->getById($customerId);
                $this->setCookieAndSession($customerInterface);

                $customerToken = $this->_tokenModelFactory->create();
                $connection = $this->resourceConnection->getConnection();
                $query = $connection->fetchAll("SELECT token FROM mobikul_oauth_token as mb where mb.customer_id =" . $customerId);

                if (isset($query)) {
                    $token = @$query[0]["token"];
                } else {
                    $token = '';
                }

                $items = 0;

                if (isset($requestData['quoteId'])) {
                    if ($requestData['quoteId']) {
                        $this->mergeQuote($requestData['quoteId'], $customerInterface, $storeId);
                        $quote = $this->quoteFactory->create()->loadByCustomer($customerInterface->getId());
                        $quoteId = $quote->getId();
                        $items = $quote->getItemsQty();
                    } else {
                        $response = [
                            'error' => true,
                            'message' => __('Quote is empty')
                        ];
                        return $resultJson->setData($response);
                    }
                } else {
                    $quote = $this->quoteFactory->create()->loadByCustomer($customer->getId());
                    $quoteId = $quote->getId();
                    $items = $quote->getItemsQty();
                }

                $response = [
                    'errors' => false,
                    'message' => __("Login successful"),
                    "success" => true,
                    "customerToken" => $token,
                    "customerId" => $customer->getId(),
                    "customerEmail" => $customer->getEmail(),
                     'countryCode' => $customer->getTelephoneCountryCode(),
                    'telephone' => ($customer->getTelephone()) ? $customer->getTelephone() : null ,
                    'isMobile' => ($customer->getTelephone() &&  $customer->getTelephoneCountryCode()) ? true : false,
                    "cartCount" => $items * 1,
                    "customerName" => $customer->getFirstname(),
                    "quote_id" => $quoteId,
                ];
            } elseif ($requestData['type'] == 'forgot-password' && $requestData['userType'] == 'telephone') {
                $this->accountModel->verifyUserOtp($customerId, $requestData['otp'], $requestData['type']);
                if ($otpRow->getId() == base64_decode($requestData['otpId'])) {

                    $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                    $connection = $resource->getConnection();
                    $otp = $otpRow->getOtp();
                    $otpId = $otpRow->getId();
                    $sql = "UPDATE acodesh_otp SET `forgot_verify` = '1'  WHERE `id` = $otpId ";
                    $connection->query($sql);
                    $response['succeess'] = true;
                    $response['message'] = __("OTP verified successfully.");
                    if ($response['succeess'] == true) {
                        $customer = $this->customerRepository->getById($customerId);
                        $this->setCookieAndSession($customer);
                        $delay = 2;
                        header("Refresh: $delay;");
                    }
                    return $resultJson->setData($response);
                }
            } elseif ($requestData['type'] == 'forgot-password' && $requestData['userType'] == 'email') {
                $customer = $this->customerRepository->getById($requestData['otpId']);
                $this->setCookieAndSession($customer);
                $delay = 2;
                header("Refresh: $delay;");
                $response['success'] = true;
                return $resultJson->setData($response);
            } else {
                try {
                    if ($requestData['firstname'] == trim($requestData['firstname']) && strpos($requestData['firstname'], ' ') !== false) {
                        $expName = explode(" ", $requestData['firstname']);
                        $firstname = $expName[0];
                    } else {
                        $firstname = $requestData['firstname'] ? $requestData['firstname'] : 'baytonia';
                        if (isset($requestData['req_device_type']) && $requestData['req_device_type'] == 'app') {
                            $lastname = $requestData['lastname'] ? $requestData['lastname'] : 'baytonia';
                        } else {
                            $lastname = '';
                        }
                    }
                    $replace = preg_replace('/[^A-Za-z0-9\-]/', '', @$requestData['telephone']);
                    $telephoneRes = substr($replace, strlen(@$requestData['countryCode']));
                    $telephone = $telephoneRes ? $telephoneRes : "";
                    if (substr($telephone, 0, 1) == 0) $telephone = substr($telephone, 1);
                    if (isset($requestData['req_device_type']) && $requestData['req_device_type'] == 'app') {
                        $password = @$requestData['password'] ? @$requestData['password'] : 'baytonia';
                    } else {
                        $password = 'baytonia';
                    }

                    $email = isset($requestData['email']) ? $requestData['email'] : null;

                    $customerInterface = $this->createCustomer->create()->setWebsiteId($websiteId)->loadByEmail($email);

                    if (is_null($email)) {
                        $response = [
                            'error' => true,
                            'message' => __('Customer email can\'t be null.')
                        ];
                        return $resultJson->setData($response);
                    }

                    if ($customerInterface->getId() > 0) {
                        $response = [
                            'error' => true,
                            'message' => __('We can\'t save the customer, already registered.')
                        ];
                        return $resultJson->setData($response);
                    }

                    $newCustomer = $this->createCustomer->create();
                    $newCustomer->setWebsiteId($websiteId);

                    $newCustomer->setEmail($email);
                    $newCustomer->setFirstname($requestData['firstname']);
                    $newCustomer->setPassword($password);
                    $newCustomer->setTelephone($telephone);
                    $newCustomer->setTelephoneCountryCode(@$requestData['countryCode']);
                    $newCustomer->save();
                    $customerSession = $objectManager->create('Magento\Customer\Model\Session');
                    $customerSession->setCustomerAsLoggedIn($newCustomer);
                    $tokenKey = $this->mobihelper->createCustomerAccessToken($newCustomer->getId());

                    $connection = $this->resourceConnection->getConnection();
                    $query = $connection->fetchAll("SELECT token FROM mobikul_oauth_token as mb where mb.customer_id =" . $newCustomer->getId());

                    if (isset($query)) {
                        $token = @$query[0]["token"];
                    } else {
                        $token = '';
                    }

                    $items = 0;
                    if (isset($requestData['quoteId']) && $requestData['quoteId']) {
                        $this->quoteManagement->assignCustomer($requestData['quoteId'], $newCustomer->getId(), $storeId);
                    } else {
                        $this->quoteManagement->createEmptyCartForCustomer($newCustomer->getId());
                    }
                    $quote = $this->quoteFactory->create()->loadByCustomer($newCustomer->getId());
                    $items = $quote->getItemsQty();

                    $quoteId = $quote->getId();

                    if (isset($requestData['req_device_type']) && $requestData['req_device_type'] == 'app') {
                        $response = [
                            'errors' => false,
                            'message' => $this->getSuccessMessage(),
                            "success" => true,
                            "customerToken" => $token,
                            "customerName" => $newCustomer->getFirstname(),
                            "customerEmail" => $newCustomer->getEmail(),
                             'countryCode' => $newCustomer->getTelephoneCountryCode(),
                            'telephone' => ($newCustomer->getTelephone()) ? $newCustomer->getTelephone() : null ,
                            'isMobile' => ($newCustomer->getTelephone() &&  $newCustomer->getTelephoneCountryCode()) ? true : false,
                            "customer_id" => $newCustomer->getId(),
                            "cartCount" => $items * 1,
                            "quoteId" => $quoteId
                        ];
                    } else {
                        $response = [
                            'errors' => false,
                            'message' => $this->getSuccessMessage(),
                            "success" => true,
                            "customerName" => $newCustomer->getFirstname(),
                            "customerEmail" => $newCustomer->getEmail(),
                             'countryCode' => $newCustomer->getTelephoneCountryCode(),
                            'telephone' => ($newCustomer->getTelephone()) ? $newCustomer->getTelephone() : null ,
                            'isMobile' => ($newCustomer->getTelephone() &&  $newCustomer->getTelephoneCountryCode()) ? true : false,
                            "customerId" => $newCustomer->getId(),
                            "cartCount" => $items * 1,
                            "quoteId" => $quoteId
                        ];
                    }

                    $requestedRedirect = $this->accountRedirect->getRedirectCookie();
                    if (!$this->scopeConfig->getValue('customer/startup/redirect_dashboard') && $requestedRedirect) {
                        $response['redirectUrl'] = $this->_redirect->success($requestedRedirect);
                        $this->accountRedirect->clearRedirectCookie();
                    }
                    $delay = 2;
                    header("Refresh: $delay;");
                    return $resultJson->setData($response);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
                $response = [
                    'errors' => false,
                    'message' => __("OTP verified successfully."),
                ];
            }
        } catch (\Exception $e) {
            $response = [
                'errors' => true,
                'message' => $e->getMessage(),
            ];
        }
        return $resultJson->setData($response);
    }

    protected function mergeQuote($quoteId, $customer, $storeId)
    {

        $guestQuote = $this->quoteFactory->create()->setStoreId($storeId)->load($quoteId);

        $customerQuote = $this->getCustomerQuoteCollection($customer->getId(), $storeId);
        if ($customerQuote->getId() > 0) {
            $customerQuote->merge($guestQuote)->collectTotals()->save();
        } else {
            $guestQuote->assignCustomer($customer);
            $guestQuote->setCustomer($customer);
            $guestQuote->getBillingAddress();
            $guestQuote->getShippingAddress()->setCollectShippingRates(true);
            $guestQuote->collectTotals()->save();
        }
    }

    public function getCustomerQuoteCollection($customerId)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $quoteCollection = $this->quoteFactory->create()->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter("customer_id", $customerId)
            ->addFieldToFilter("is_active", 1)
            ->addOrder("updated_at", "DESC");
        return $quoteCollection->getFirstItem();
    }

}
