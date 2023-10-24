<?php
namespace InformaticsCommerce\ProductAskQuestions\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;


class Questions extends  Action
{

    protected $_pageFactory;
    public function __construct(PageFactory $pageFactory, Context $context)
    {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
   {
       $resultPage = $this->_pageFactory->create();
       $resultPage->setActiveMenu('Magento_Catalog::catalog_products');
       $resultPage->getConfig()->getTitle()->prepend(__('Questions List'));
        return $resultPage;
   }
}
