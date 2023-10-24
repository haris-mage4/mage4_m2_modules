<?php

namespace Baytonia\ExportSalableProduct\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Sales\Model\Order;

class CreateShipment extends \Magento\Backend\App\Action
{
    const XML_PATH_START_FROM = 'exportproduct/create_shipment_export/increment_id_from';
    const XML_PATH_END_AT = 'exportproduct/create_shipment_export/increment_id_to';

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection $resource
     */
    protected $resource;
    
    /**
     * @var \Magento\Framework\App\ResponseInterface $response
     */
    protected $response;

    /**
     * @var GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;


    /**
     * Allproducts constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ResponseInterface $response,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        JsonFactory $resultJsonFactory
    ) {
        $this->fileFactory   = $fileFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resource = $resource;
        $this->response = $response;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->scopeConfig = $scopeConfig;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Export action from import/export
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $headers  = $this->getHeaders();
        $template = $this->getStringCsvTemplate($headers);
        // Add header (titles)
        $content[] = $headers->toString($template);
        $data = array();
        $exportSalableProduct = array();
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $incFrom = $this->scopeConfig->getValue(self::XML_PATH_START_FROM, $storeScope);
        $incTo = $this->scopeConfig->getValue(self::XML_PATH_END_AT, $storeScope);
        $objectManager = ObjectManager::getInstance();

        if(empty($incFrom) || empty($incTo)){
            return $resultJson->setData(['message' => 'error', 'text' => 'Order InrementId From and To is required.if already entered please press the Save Config button']);
        }

        if($incTo < $incFrom){
            return $resultJson->setData(['message' => 'error', 'text' => 'Order InrementId To must be greater than Order InrementId From']);
        }

        $getOrderIds = $this->getOrderIds($incFrom,$incTo);
        if(empty($getOrderIds)){
            return $resultJson->setData(['message' => 'error', 'text' => 'No Order Id found.Please check the input range and try againn']);
        }

        $connection = $this->resource->getConnection();
        foreach ($getOrderIds as $getOrderId) {
             $getShipmentId = $this->getShipmentId($getOrderId['entity_id']);
             if(!empty($getShipmentId)){
                $exportSalableProduct['inrement_id'] = '"' . $getOrderId['increment_id'] . '"';
                $exportSalableProduct['shipment_details'] = 'Shipment Already Exist';
             }else{

                $exportSalableProduct['inrement_id'] = '"' . $getOrderId['increment_id'] . '"';
                $order = $objectManager->create('Magento\Sales\Model\Order')->loadByAttribute('increment_id', $getOrderId['increment_id']);
                
                if($order->isCanceled()){
                    $state = Order::STATE_PROCESSING;
                    $productStockQty = [];
                    foreach ($order->getAllVisibleItems() as $item) {
                        $productStockQty[$item->getProductId()] = $item->getQtyCanceled();
                        foreach ($item->getChildrenItems() as $child) {
                            $productStockQty[$child->getProductId()] = $item->getQtyCanceled();
                            $child->setQtyCanceled(0);
                            $child->setTaxCanceled(0);
                            $child->setDiscountTaxCompensationCanceled(0);
                        }
                        $item->setQtyCanceled(0);
                        $item->setTaxCanceled(0);
                        $item->setDiscountTaxCompensationCanceled(0);
                    }

                    $order->setSubtotalCanceled(0);
                    $order->setBaseSubtotalCanceled(0);
                    $order->setTaxCanceled(0);
                    $order->setBaseTaxCanceled(0);
                    $order->setShippingCanceled(0);
                    $order->setBaseShippingCanceled(0);
                    $order->setDiscountCanceled(0);
                    $order->setBaseDiscountCanceled(0);
                    $order->setTotalCanceled(0);
                    $order->setBaseTotalCanceled(0);
                    $order->setState($state)
                        ->setStatus($order->getConfig()->getStateDefaultStatus($state));
                    if (!empty($comment)) {
                        $order->addStatusHistoryComment($comment, false);
                    }

                    $order->setInventoryProcessed(true);

                    $order->save();
                }
                
                // Initialize the order shipment object
                $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
                $shipment = $convertOrder->toShipment($order);
                // Loop through order items
                foreach ($order->getAllItems() AS $orderItem) {
                    // Check if order item has qty to ship or is virtual
                    if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                        continue;
                    }
                    $qtyShipped = $orderItem->getQtyToShip();
                    // Create shipment item with qty
                    $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                    // Add shipment item to shipment
                    $shipment->addItem($shipmentItem);
                }

                // Register shipment
                $shipment->register();
                $shipment->getOrder()->setIsInProcess(true);

                try {
                    // Save created shipment and order
                    $shipment->save();
                    $shipment->getOrder()->save();
                    $exportSalableProduct['shipment_details'] = "Shipment Generated Succesfully";
                } catch (\Exception $e) {
                   $exportSalableProduct['shipment_details'] = "Shipment Not Created: ". $e->getMessage();
                }
            }

            $content[] = implode(",", $exportSalableProduct);
        }

        $contentAsAString = implode("\n", $content);
        $outputFile = 'exportordershipment_' . date('Y-m-d') . '_' . time() . '.csv';
        return $this->fileFactory->create(
            $outputFile,
            $contentAsAString,
            DirectoryList::VAR_DIR
        );
        
        return $resultJson->setData(['message' => 'success', 'text' => 'success']);
    }


    /**
     * Get headers for the selected entities
     *
     * @return \Magento\Framework\DataObject
     */
    private function getHeaders()
    {
        $dataFields = [
            'increment_id'             => __('Increment Id'),
            'shipment_details'         => __('Shipment Details'),
        ];

        $dataObject = $this->dataObjectFactory->create($dataFields);

        return $dataObject;
    }

    /**
     * Create data template from headers
     *
     * @param \Magento\Framework\DataObject $headers
     * @return string
     */
    private function getStringCsvTemplate(\Magento\Framework\DataObject $headers)
    {
        $data         = $headers->getData();
        $templateData = [];
        foreach ($data as $propertyKey => $value) {
            $templateData[] = '"{{' . $propertyKey . '}}"';
        }
        $template = implode(',', $templateData);

        return $template;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrderIds($from,$to)
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT so.entity_id AS 'entity_id',so.increment_id AS 'increment_id'
                FROM sales_order so where increment_id >= '".$from."' AND increment_id <= '".$to."'";

        return $connection->fetchAll($sql);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getShipmentId($id)
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT ss.entity_id AS 'entity_id'
                FROM sales_shipment ss where order_id = '".$id."'";
        return $connection->fetchOne($sql);
    }
}