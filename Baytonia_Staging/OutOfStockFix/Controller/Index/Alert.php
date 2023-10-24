<?php
namespace Baytonia\OutOfStockFix\Controller\Index;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Baytonia\OutOfStockFix\Model\Alert as StockModel;
class Alert extends \Magento\Framework\App\Action\Action
{
	protected $_pageFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StockModel $stockModel,
		\Magento\Framework\View\Result\PageFactory $pageFactory)
	{
		$this->_pageFactory = $pageFactory;
        $this->_stockmodel = $stockModel;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
		return parent::__construct($context);
	}

	public function execute()
    {
        $backUrl = $this->getRequest()->getPost("alert_subscribe_url");
        $email = $this->getRequest()->getPost("alert_subscribe_email");
        $productId = (int)$this->getRequest()->getPost('alert_subscribe_pid');
        $productUrl = $this->getRequest()->getPost("alert_subscribe_backurl");
        
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (!$backUrl || !$productId) {
            $resultRedirect->setPath('/');
            return $resultRedirect;
        }

        try {
            /* @var $product \Magento\Catalog\Model\Product */
            $product = $this->productRepository->getById($productId);
            $store = $this->storeManager->getStore();
            /** @var \Magento\ProductAlert\Model\Stock $model */
            
            $alreadyExitsModel = $this->_stockmodel->getCollection()
            ->addFieldToFilter("product_id",$product->getId())
            ->addFieldToFilter("store_id",$store->getId())
            ->addFieldToFilter("sync_status",0);
            
            if(count($alreadyExitsModel->getData())){
                $this->messageManager->addErrorMessage(__('You already subscribed for this alert.'));
            }else{
                $model = $this->_stockmodel
                ->setEmail($email)
                ->setProductId($product->getId())
                ->setWebsiteId($store->getWebsiteId())
                ->setStoreId($store->getId())
                ->setSyncStatus(0)
                ->setUpdatedAt(date("Y-m-d H:i:s"));
            $model->save();
            $this->messageManager->addSuccessMessage(__('Alert subscription has been saved.'));
            }
            
            
            
        } catch (NoSuchEntityException $noEntityException) {
            $this->messageManager->addErrorMessage(__('There are not enough parameters.'));
            $resultRedirect->setUrl($productUrl);
            return $resultRedirect;
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage(
                $e,
                __("The alert subscription couldn't update at this time. Please try again later. %1 " , $e->getMessage())
            );
        }
        
        $resultRedirect->setUrl($productUrl);
        return $resultRedirect;
    }
}