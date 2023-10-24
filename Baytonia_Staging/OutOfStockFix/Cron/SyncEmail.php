<?php
namespace Baytonia\OutOfStockFix\Cron;
use Baytonia\OutOfStockFix\Model\Alert as StockModel;
use Baytonia\OutOfStockFix\Model\AlertFactory as StockModelFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\App\Emulation;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\ProductAlert\Model\ProductSalability;
use Magento\Store\Model\ScopeInterface;
use Magento\ProductAlert\Helper\Data;
use Magento\ProductAlert\Block\Email\Stock;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Area;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;

class SyncEmail
{
    
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockModel $stockModel,
        StockModelFactory $stockModelFactory,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        Emulation $appEmulation,
        Data $productAlertData,
        ResourceConnection $resourceConnection,
        \Magento\Store\Model\Website $websiteModel,
        \Magento\Customer\Model\CustomerFactory $customer,
        ProductSalability $productSalability = null
        )
	{
		$this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_transportBuilder = $transportBuilder;
        $objectManager = ObjectManager::getInstance();
        $this->_websiteModel = $websiteModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_appEmulation = $appEmulation;
        $this->_productAlertData = $productAlertData;
        $this->productSalability = $productSalability ?: $objectManager->get(ProductSalability::class);
        $this->_stockmodel = $stockModel;
        $this->customer = $customer;
        $this->_stockmodelfactory = $stockModelFactory;
        
        $this->resourceConnection = $resourceConnection;
	}

    public function execute()
    {
        
        $collections = $this->_stockmodel->getCollection()
        ->addFieldToFilter("sync_status",0)
        ->addFieldToFilter("type",0);
        
        foreach ($collections as $alert) {
                try {
                    $storeId = $alert->getStoreId();
                    $websiteId = $alert->getWebsiteId();
                    $website = $this->_websiteModel->load($websiteId,'website_id');
                    $product = $this->productRepository->getById(
                        $alert->getProductId(),
                        false,
                        $storeId
                    );

                    $product->setCustomerGroupId(1);

                    if ($this->productSalability->isSalable($product, $website)) {
                        
                        $this->send($product,$storeId,$alert->getEmail());
                        $alert->setSyncStatus(1);
                        $alert->setSendDate(date("Y-m-d H:i:s"));
                        $alert->save();
                    }
                } catch (\Exception $e) {
                    throw $e;
                }
            }
    }
    
    public function mergeTable()
    {
        $collectionss = $this->_stockmodelfactory->create()->getCollection()->setOrder("foreign_id","DESC")->getFirstItem();
        $lastsyncid = (int)$collectionss->getData("foreign_id");
        
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('product_alert_stock');
        //For Select query
        $query = "Select * FROM " . $table . " WHERE `alert_stock_id` >= $lastsyncid LIMIT 300";
        $result = $connection->fetchAll($query);
        
        
        
        foreach($result as $result){
            $stockalertId = $result["alert_stock_id"];
            
            $modelload = $this->_stockmodelfactory->create()->load($stockalertId,"foreign_id");
            if(!$modelload->getAlertId()){
                
                $_email = "";
                $_name = "";
                
               $_cst = $this->customer->create()->load($result["customer_id"]);
               if($_cst->getId()){
                $_email = $_cst->getEmail();
                $_name = trim($_cst->getFirstname() . " " . $_cst->getLastname());
               }
                
                
                
                $product = $this->productRepository->getById(
                        $result["product_id"],
                        false,
                        $result["store_id"]
                    );
                
                
               $model = $this->_stockmodelfactory->create();
            $dataArray = array();
            
            $dataArray["product_id"] = $result["product_id"];
            $dataArray["customer_id"] = $result["customer_id"];
            $dataArray["customer_name"] = $_name;
            $dataArray["email"] = $_email;
            $dataArray["store_id"] = $result["store_id"];
            $dataArray["website_id"] = $result["website_id"];
            $dataArray["send_date"] = $result["send_date"];	
            $dataArray["foreign_id"] = $stockalertId;
            $dataArray["product_name"] = $product->getName();
            $dataArray["type"] = 1;
            //echo $result["alert_stock_id"] . "\n";
            $model->addData($dataArray)->save();
            unset($model); 
                        }
            
            
            
        }
    }
    
    
    public function send($product,$storeId,$email)
    {

        $templateConfigPath = \Magento\ProductAlert\Model\Email::XML_PATH_EMAIL_STOCK_TEMPLATE;
        if (!$templateConfigPath) {
            return false;
        }
        
        $store = $this->_storeManager->getStore($storeId);

        $this->_appEmulation->startEnvironmentEmulation($storeId);

        $block = $this->_productAlertData->createBlock(Stock::class);
        $block->setStore($store)->reset();

        $product->setCustomerGroupId(1);
        $block->addProduct($product);

        $templateId = $this->_scopeConfig->getValue(
            $templateConfigPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $alertGrid = ObjectManager::getInstance()->get("\Magento\Framework\App\State")->emulateAreaCode(
            Area::AREA_FRONTEND,
            [$block, 'toHtml']
        );
        $this->_appEmulation->stopEnvironmentEmulation();

        $customerName = "Guest";
        $this->_transportBuilder->setTemplateIdentifier(
            $templateId
        )->setTemplateOptions(
            ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
        )->setTemplateVars(
            [
                'customerName' => $customerName,
                'alertGrid' => $alertGrid,
            ]
        )->setFromByScope(
            $this->_scopeConfig->getValue(
                \Magento\ProductAlert\Model\Email::XML_PATH_EMAIL_IDENTITY,
                ScopeInterface::SCOPE_STORE,
                $storeId
            ),
            $storeId
        )->addTo(
            $email,
            $customerName
        )->getTransport()->sendMessage();

        return true;
    }
}
