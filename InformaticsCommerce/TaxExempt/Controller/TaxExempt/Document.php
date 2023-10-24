<?php
namespace InformaticsCommerce\TaxExempt\Controller\TaxExempt;

use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Api\CustomerRepositoryInterface;


class Document extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface {

    protected  $_request;
    protected  $_uploaderFactory;

    protected  $_storeManager;
    protected  $_customerSession;
    protected  $_customerRepository;

    public function __construct(\Magento\Framework\App\Action\Context $context, Http $request, \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory, StoreManagerInterface $storeManager, Session $customerSession, CustomerRepositoryInterface $customerRepository)
    {
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_uploaderFactory = $uploaderFactory;
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $customerId =$this->_customerSession->getCustomer()->getId();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
//            $uploader = $this->_uploaderFactory->create(['fileId' => 'tax_file']);
//            $uploader->setAllowedExtensions(['pdf']);
//            $uploader->setAllowRenameFiles(true);
//            $uploader->setFilesDispersion(false);
//
//            $result = $uploader->save(getcwd().'/media/tax/files/customers');
            $customer = $this->_customerRepository->getById($customerId);
           // $customer->setCustomAttribute('tax_exempt_file', $result['file']);
            $this->_customerRepository->save($customer);
           /* if ($result['file']) {

                // File successfully uploaded and saved. Perform any additional logic here.
                $this->messageManager->addSuccessMessage(__('Document uploaded successfully.'));
            } else {
                $this->messageManager->addErrorMessage(__('An error occurred while uploading the document.'));
            } */
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('customer/account');
    }
}
