<?php

namespace Baytonia\BankInstallment\Controller\Adminhtml\Bank;

use \Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException as FrameworkException;
use Magento\Framework\App\Filesystem\DirectoryList;
use \Magento\Framework\Controller\ResultFactory;

class Save extends \Baytonia\BankInstallment\Controller\Adminhtml\Bank {

    protected $uploaderFactory;
    protected $imageModel;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory, 
        \Baytonia\BankInstallment\Model\Bank\Image $imageModel, 
        \Baytonia\BankInstallment\Model\BankFactory $bankFactory, 
        \Baytonia\BankInstallment\Model\ResourceModel\Bank\CollectionFactory $bankCollectionFactory, 
        \Magento\Framework\Registry $coreRegistry, 
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory, 
        \Magento\Framework\View\Result\PageFactory $resultPageFactory, 
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory, 
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Backend\Helper\Js $jsHelper
    ) {
        parent::__construct($context, $bankFactory, $bankCollectionFactory, 
                $coreRegistry, $fileFactory, $resultPageFactory, $resultLayoutFactory, 
                $resultForwardFactory, $storeManager, $jsHelper);

        $this->uploaderFactory = $uploaderFactory;
        $this->imageModel = $imageModel;
    }

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute() {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_bankFactory->create();

            if ($id = $this->getRequest()->getParam(static::PARAM_CRUD_ID)) {
                $model->load($id);
            }

            $imageRequest = $this->getRequest()->getFiles('image');
            $fileName = isset($imageRequest['name']) && strlen($imageRequest['name']) > 0 
                            ? $imageRequest['name'] : '';
            
            $isUpload = false;
            //uploading with file name
            if ( $fileName <> '' ) {
                $isUpload = TRUE;
                if (strlen($fileName) > 90) {
                    $this->messageManager->addErrorMessage( 
                            __($fileName . ' was not uploaded. Filename is too long; must be 90 characters or less.'));
                    $fileName = '';
                }
            }
            //uploading without file name
            else {
                //bank has exiting image
                if (isset($data['image']) && isset($data['image']['value'])) {
                    if (isset($data['image']['delete'])) {
                        $data['image'] = null;
                        $data['delete_image'] = true;
                    } elseif (isset($data['image']['value'])) {
                        $data['image'] = $data['image']['value'];
                    } else {
                        $data['image'] = null;
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('An image for Bank is required!'));
                    $fileName = '';
                    $isUpload = TRUE;
                }
            }
            
            $this->_getSession()->unsBankName();
            $this->_getSession()->unsImageAlt();
            
            if ($isUpload) {
                if ($fileName == '') {
                    foreach($data as $key=>$val ){
                        if($key == 'name')
                            $key = 'bank_name';
                        $this->_getSession()->setData($key, $val);
                    }
                    
                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirect;
                } else {
                    $data['image'] = $this->uploadImage('image', $this->imageModel->getBaseDir(\Baytonia\BankInstallment\Model\Bank\Image::BASE_MEDIA_PATH), $data);
                }
            }
            
            $data['store_id'] = isset($data['store_id'][0])?$data['store_id'][0]:$data['store_id'];

            $model->setData($data);

            try {
                $model->save();

                $this->messageManager->addSuccess(__('The bank has been saved.'));
                $this->_getSession()->setFormData(false);

                return $this->_getBackResultRedirect($resultRedirect, $model->getId());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the bank.'));
            }

            $this->_getSession()->setFormData($data);

            return $resultRedirect->setPath(
                            '*/*/edit', [static::PARAM_CRUD_ID => $this->getRequest()->getParam(static::PARAM_CRUD_ID)]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Upload file and return file name 
     */
    public function uploadImage($input, $destinationFolder, $data) {
        try {
            
            if (isset($data[$input]['delete'])) {
                return '';
            } else {
                $uploader = $this->uploaderFactory->create(['fileId' => $input]);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);
                $result = $uploader->save($destinationFolder);
                return \Baytonia\BankInstallment\Model\Bank\Image::BASE_MEDIA_PATH . $result['file'];
            }
        } catch (\Exception $e) {
            if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                throw new FrameworkException($e->getMessage());
                $this->messageManager->addErrorMessage(__($e->getMessage()));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                return $resultRedirect;
               
            } else {
                if (isset($data[$input]['value'])) {
                    return $data[$input]['value'];
                }
            }
        }
        return '';
    }

}
