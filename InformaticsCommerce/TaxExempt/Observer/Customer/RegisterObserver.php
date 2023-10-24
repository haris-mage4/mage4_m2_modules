<?php

namespace InformaticsCommerce\TaxExempt\Observer\Customer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Psr\Log\LoggerInterface;

class RegisterObserver implements ObserverInterface
{
    protected $_request;
    protected $_logger;
    protected $_cusomerRepository;
    protected $_uploaderFactory;

    public function __construct(\Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,CustomerRepositoryInterface $customerRepository, LoggerInterface $logger, RequestInterface $request)
    {
        $this->_uploaderFactory = $uploaderFactory;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_cusomerRepository = $customerRepository;
    }

    public function execute(Observer $observer)
    {
        $isTaxExempt = $this->_request->getParam('tax_exempt');
        $taxExemptNumber = $this->_request->getParam('tax_exempt_number');
      /**  $tax_file = $this->_request->getParam('tax_file');

        $uploader = $this->_uploaderFactory->create(['fileId' => $tax_file]);
        $uploader->setAllowedExtensions(['pdf']);
        $uploader->setAllowRenameFiles(true);
        $uploader->setFilesDispersion(false);

        $result = $uploader->save(getcwd().'/media/tax/files/customers'); **/

        $customerId = $observer->getData('customer')->getId();
        $customer = $this->_cusomerRepository->getById($customerId);
        if ($isTaxExempt === "Yes") {
            $customer->setGroupId(4);
            $customer->setCustomAttribute('tax_exempt_number', $taxExemptNumber);
//            $customer->setCustomAttribute('tax_exempt_file', $result['file']);
        }
        try{
            $this->_cusomerRepository->save($customer);
        }catch (LocalizedException $exception){
            $this->_logger->info($exception->getMessage(). "333");
        }
    }
}
