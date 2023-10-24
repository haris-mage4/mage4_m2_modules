<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Baytonia\CustomSearch\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Catalog index page controller.
 */
class Index extends \Magento\Framework\App\Action\Action  implements HttpGetActionInterface
{
	protected $_pageFactory;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
		return parent::__construct($context);
	}

	public function execute()
	{
        $resultPage = $this->_pageFactory->create();
        $data  = $this->getRequest()->getParam('q');
        $block = $resultPage->getLayout()->getBlock('result_display');
        $block->setData('queryString', $data);
        return $resultPage;
	}
}