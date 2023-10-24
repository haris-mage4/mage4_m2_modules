<?php

namespace InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;

use Exception;
use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterfaceFactory;
use InformaticsCommerce\UseMyShippingAccount\Api\DataRepositoryInterface;
use InformaticsCommerce\UseMyShippingAccount\Controller\Adminhtml\Option;
use InformaticsCommerce\UseMyShippingAccount\Model\Data as DataModel;
use InformaticsCommerce\UseMyShippingAccount\Model\ResourceModel\Data\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

abstract class MassAction extends Option
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var DataRepositoryInterface
     */
    protected $dataRepository;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var string
     */
    protected $successMessage;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * MassAction constructor.
     *
     * @param Filter $filter
     * @param Registry $registry
     * @param DataRepositoryInterface $dataRepository
     * @param PageFactory $resultPageFactory
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param ForwardFactory $resultForwardFactory
     * @param $successMessage
     * @param $errorMessage
     */
    public function __construct(Filter $filter,CollectionFactory $collectionFactory, Registry $registry, PageFactory $resultPageFactory, ForwardFactory $resultForwardFactory, Context $context, DataRepositoryInterface $dataRepository, DataInterfaceFactory $dataFactory, DataObjectHelper $dataObjectHelper)
    {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($registry, $resultPageFactory, $resultForwardFactory, $context, $dataRepository, $dataFactory, $dataObjectHelper);
    }

    /**
     * @param DataModel $data
     * @return mixed
     */
    abstract protected function massAction(DataModel $data);

    /**
     * @return Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $data) {
                $this->massAction($data);
            }
            $this->messageManager->addSuccessMessage(__($this->successMessage, $collectionSize));
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($this->errorMessage));
        }
        $redirectResult = $this->resultRedirectFactory->create();
        $redirectResult->setPath('shippingoptions/option/index');
        return $redirectResult;
    }
}
