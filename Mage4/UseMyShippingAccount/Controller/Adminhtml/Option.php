<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml;

use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterfaceFactory;
use InformaticsCommerce\UseMyShippingAccount\Api\DataRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class Option extends Action
{
    protected $dataFactory;
    protected $dataObjectHelper;

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ACTION_RESOURCE = 'nformaticsCommerce_UseMyShippingAccount::menu';

    /**
     * Data repository
     *
     * @var DataRepositoryInterface
     */
    protected $dataRepository;

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * Result Page Factory
     *
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * Result Forward Factory
     *
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Data constructor.
     *
     * @param Registry $registry
     * @param DataRepositoryInterface $dataRepository
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Context $context
     */
    public function __construct(
        Registry                $registry,
        PageFactory             $resultPageFactory,
        ForwardFactory          $resultForwardFactory,
        Context                 $context,
        DataRepositoryInterface $dataRepository,
        DataInterfaceFactory    $dataFactory,
        DataObjectHelper        $dataObjectHelper
    )
    {
        $this->coreRegistry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->dataRepository = $dataRepository;
        $this->dataFactory = $dataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context);
    }
}
