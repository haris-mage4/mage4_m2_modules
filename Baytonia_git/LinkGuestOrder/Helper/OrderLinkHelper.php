<?php

namespace Baytonia\LinkGuestOrder\Helper;

use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Ui\Model\Export\MetadataProvider;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Directory\Model\RegionFactory;

class OrderLinkHelper extends AbstractHelper
{

    private const PATTERN_NAME = '/(?:[\p{L}\p{M}\,\-\_\.\'\s\d]){1,255}+/u';

    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var MetadataProvider
     */
    protected $metadataProvider;
    /**
     * @var Filter
     */
    protected $filter;
    /**
     * @var CustomerRepository
     */
    private $repository;
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var OrderCustomerManagementInterface
     */
    protected $orderCustomerService;

    /**
     * @var Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $addressRepository;

    /**
     * @var \Magento\Customer\Api\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Customer
     * @var Customer
     */
    protected $customers;

    /**
     * Helper Customer
     */
    protected $helperCustomer;

    protected $helperConvertAddress;
    protected $regionFactory;

    /**
     * StoreManager Interface
     * @var StoreManagerInterface $storeManager
     */
    protected $storeManager;

    /**
     * Webkul\Odoomagentoconnect\Model\Customer
     */
    protected $customerModel;

    /**
     * \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer
     */
    protected $customerMapping;


    public function __construct(
        Context $context,
        RequestInterface $request,
        MetadataProvider $metadataProvider,
        Filter $filter,
        CustomerRepository $repository,
        ManagerInterface $messageManager,
        AccountManagementInterface $accountManagement,
        OrderCustomerManagementInterface $orderCustomerService,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        CustomerRepositoryInterface $customerRepository,
        Customer $customers,
        \Bss\GuestToCustomer\Helper\Customer\SaveCustomer $helperCustomer,
        \Bss\GuestToCustomer\Helper\Customer\Address $helperConvertAddress,
        StoreManagerInterface $storeManager,
        RegionFactory $regionFactory,
        \Webkul\Odoomagentoconnect\Model\Customer $customerModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Customer $customerMapping
    ) {
        parent::__construct($context);
        $this->request = $request;
        $this->metadataProvider = $metadataProvider;
        $this->filter = $filter;
        $this->repository = $repository;
        $this->messageManager = $messageManager;
        $this->accountManagement = $accountManagement;
        $this->orderCustomerService = $orderCustomerService;
        $this->addressRepository = $addressRepository;
        $this->addressDataFactory = $addressDataFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->customerRepository = $customerRepository;
        $this->customers = $customers;
        $this->helperCustomer = $helperCustomer;
        $this->helperConvertAddress = $helperConvertAddress;
        $this->storeManager = $storeManager;
        $this->regionFactory = $regionFactory;
        $this->customerModel = $customerModel;
        $this->customerMapping = $customerMapping;
    }

    /**
     * @return SearchResultInterface | Order[]
     */
    protected function getCollection()
    {
        try {
            $component = $this->filter->getComponent();
            $this->filter->prepareComponent($component);
            $this->filter->applySelectionOnTargetProvider();
            $dataProvider = $component->getContext()->getDataProvider();
            $dataProvider->setLimit(0, false);
            return $dataProvider->getSearchResult();
        } catch (LocalizedException $e) {
            return [];
        }
    }

    /**
     * @return void
     */
    public function processGuestOrders()
    {
        $regionFac = ObjectManager::getInstance()->create('Magento\Directory\Model\Region');
        foreach($this->getCollection() as $order) {
            $email = $order->getCustomerEmail();
            $specialCharacter = 0;

            if ($this->accountManagement->isEmailAvailable($order->getCustomerEmail())) { //Not exists customer will create new account
                
                $shippingAddress = $order->getShippingAddress();
                $billingAddress = $order->getBillingAddress();

                if ($this->isValidName($billingAddress->getLastname())) {
                    $arrBillingAddress = $billingAddress->getData();
                    $arrShippingAddress = $shippingAddress ? $shippingAddress->getData() : [];
                    if (!is_array($arrShippingAddress) || empty($arrShippingAddress)) {
                        $arrShippingAddress = $arrBillingAddress;
                    }
                    
                    $regionObjBil = $this->regionFactory->create()->load($billingAddress->getRegionId());
                    if(empty($regionObjBil->getData())){
                        $arrBillingAddress['region_id'] = '';
                        $arrBillingAddress['region'] = '';
                    }

                    if ($order->getShippingAddress()) {
                        $regionObjSh = $this->regionFactory->create()->load($shippingAddress->getRegionId());
                        if(empty($regionObjSh->getData())){
                            $arrShippingAddress['region_id'] = '';
                            $arrShippingAddress['region'] = '';
                        }
                    }else{
                        $arrShippingAddress['region_id'] = '';
                        $arrShippingAddress['region'] = '';
                    }

                    $customerData = $this->getCustomerData($arrBillingAddress);
                    $addresses = $this->helperConvertAddress->processAddressCustomer($arrShippingAddress, $arrBillingAddress);
                    $customer = $this->helperCustomer->processCreateCustomer($customerData, $addresses);
                    $idCustomer = $customer->getId();

                    if ($idCustomer) {
                        //sync customer to odoo
                        /*$mapping = $this->customerModel->getCollection()
                                ->addFieldToFilter('address_id', ['eq'=>'customer'])
                                ->addFieldToFilter('magento_id', ['eq'=>$idCustomer]);
                        if ($mapping->getSize() == 0) {
                            $this->customerMapping->exportSpecificCustomer($idCustomer);
                        }*/

                        $customerData['group_id'] = $customer->getGroupId();

                        //Set Order
                        $order->setCustomerId($idCustomer)
                            ->setCustomerEmail($customerData['email'])
                            ->setCustomerFirstname($customerData['firstname'])
                            ->setCustomerLastname($customerData['lastname'])
                            ->setCustomerIsGuest(0)
                            ->setCustomerGroupId($customer->getGroupId());

                        //Set order address
                        $this->setOrderAddress($idCustomer, $order);
                        $order->save();
                    }
                }
            }else {
                $customer = $this->repository->get($email);
                $order->setCustomerId($customer->getId());
                $order->setCustomerIsGuest(0);
                $order->save();
            }
        }
        $this->messageManager->addSuccessMessage(
            __('Order Linked Successfully.')
        );
    }


    /**
     * @param int $customerId
     * @param Magento\Sales\Api\Data\OrderInterface $order
     *
     * @return void
     */
    public function saveCustomerAddress($customerId, $order)
    {
        $address = $this->addressDataFactory->create();
        $region = $this->regionDataFactory->create();
        if($customerId && $order && $order->getEntityId()){
            $billingAddress = $order->getBillingAddress();
            if($billingAddress){
                $firstName = $billingAddress->getFirstname();
                $lastName = $billingAddress->getLastname();
                $regionId = $billingAddress->getRegionId();
                $regionName = $billingAddress->getRegion();
                $regionObj = $this->regionFactory->create()->load($regionId);

                $region->setRegion($regionName);
                $countryId = $billingAddress->getCountryId();
                $city = $billingAddress->getCity();
                $postcode = $billingAddress->getPostcode();
                $street = $billingAddress->getStreet();
                $telephone = $billingAddress->getTelephone();

                $address->setFirstname($firstName);
                $address->setLastname($lastName);
                $address->setCountryId($countryId);
                if(!empty($regionObj->getData())){
                    $address->setRegionId($regionId)
                    ->setRegion($region);
                }
 
                $address->setCity($city)
                        ->setPostcode($postcode)
                        ->setCustomerId($customerId)
                        ->setStreet($street)
                        ->setTelephone($telephone)
                        ->setIsDefaultBilling(true)
                        ->setIsDefaultShipping(true);

                $this->addressRepository->save($address);
            }
        }
    }

    /**
     * @param int $orderId
     *
     * @return Magento\Customer\Api\CustomerRepositoryInterface | boolean
     */
    public function convertGuestToCustomer($orderId){ 
        $customer = $this->orderCustomerService->create($orderId);
        return $customer;
    }

    /**
     * Check if name field is valid.
     *
     * @param string|null $nameValue
     * @return bool
     */
    private function isValidName($nameValue)
    {
        if ($nameValue != null) {
            if (preg_match(self::PATTERN_NAME, $nameValue, $matches)) {
                return $matches[0] == $nameValue;
            }
        }

        return true;
    }

    /**
     * GetCustomerData
     *
     * @param array $arrBillingAddress
     * @return array
     */
    protected function getCustomerData($arrBillingAddress)
    {
        $storeManager = $this->storeManager;
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $storeId = $storeManager->getStore()->getId();
        $customerData =
            [
                "website_id" => $websiteId,
                'store_id' => $storeId,
                "group_id" => 1,
                "disable_auto_group_change" => 0,
                "prefix" => $arrBillingAddress['prefix'],
                "firstname" => $arrBillingAddress['firstname'],
                "lastname" => $arrBillingAddress['lastname'],
                "suffix" => $arrBillingAddress['suffix'],
                "email" => $arrBillingAddress['email'],
                "fax" => $arrBillingAddress['fax'],
                'telephone' => $arrBillingAddress['telephone'],
                'company' => $arrBillingAddress['company'],
                "sendemail_store_id" => 1
            ];
        return $customerData;
    }

    /**
     * SetOrderAddress
     *
     * @param $idCustomer
     * @param $order
     */
    protected function setOrderAddress($idCustomer, $order)
    {
        $idDefaultBilling = $this->customers->load($idCustomer)->getDefaultBilling();
        $idDefaultShipping = $this->customers->load($idCustomer)->getDefaultShipping();
        if ($order->getBillingAddress()) {
            $order->getBillingAddress()->setCustomerId($idCustomer);
            $order->getBillingAddress()->setCustomerAddressId($idDefaultBilling);
        }
        if ($order->getShippingAddress()) {
            $order->getShippingAddress()->setCustomerId($idCustomer);
            $order->getShippingAddress()->setCustomerAddressId($idDefaultShipping);
        }
    }
}
