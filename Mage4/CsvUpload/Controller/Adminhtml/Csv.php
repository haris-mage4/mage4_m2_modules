<?php

namespace Mage4\CsvUpload\Controller\Adminhtml;

/**
 * Csv abstract class
 */
abstract class Csv extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Mage4_CsvUpload::top_level';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    /**
     * Init page
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function initPage($resultPage)
    {
        $resultPage->setActiveMenu(self::ADMIN_RESOURCE)
            ->addBreadcrumb(__('Mage4'), __('Mage4'))
            ->addBreadcrumb(__('Csv'), __('Csv'));
        return $resultPage;
    }
}
