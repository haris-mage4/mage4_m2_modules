<?php

namespace InformaticsCommerce\TaxExempt\Controller\TaxExempt;

use Magento\Checkout\Model\Session;
use Magento\Customer\Model\Group;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;


class Apply extends Action implements HttpPostActionInterface
{
    protected $_jsonFactory;
    protected $_http;
    protected $_quoteRepository;
    protected $_session;
    protected $_group;
    protected $_uploaderFactory;

    public function __construct(Context $context, JsonFactory $jsonFactory, Http $http, CartRepositoryInterface $quoteRepository, Session $session, Group $group,\Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory)
    {
        $this->_jsonFactory = $jsonFactory;
        $this->_http = $http;
        $this->_quoteRepository = $quoteRepository;
        $this->_session = $session;
        $this->_group = $group;
        $this->_uploaderFactory = $uploaderFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->_jsonFactory->create();
        $tax_exempt_number = $this->_http->getParam('tax_exempt_number');
        try {
//            $uploader = $this->_uploaderFactory->create(['fileId' => 'tax_exempt_file']);
//            $uploader->setAllowedExtensions(['pdf']);
//            $uploader->setAllowRenameFiles(true);
//            $uploader->setFilesDispersion(false);
//            $file = $uploader->save(getcwd().'/media/tax/files/orders');

            $this->_session->getQuote()
                ->setCustomerGroupId(4)
                ->setCustomerTaxClassId(4)
                ->setTaxExemptNumber($tax_exempt_number)
                //->setUploadDocument($file['file'])
                ->collectTotals()->save();
            $result->setData(['success' => true, 'message' =>'tax exempted']);


        }catch (LocalizedException $exception){
            $result->setData(['success' => false, 'message' => $exception->getMessage()]);
        }
            return $result;
    }
}
