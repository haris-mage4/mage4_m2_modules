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

class Orderedproducts extends \Magento\Backend\App\Action
{
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
     * Allproducts constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param GetSalableQuantityDataBySku $getSalableQuantityDataBySku
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ResponseInterface $response,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->fileFactory   = $fileFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resource = $resource;
        $this->response = $response;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
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
        $response = array();
        $headers  = $this->getHeaders();
        $template = $this->getStringCsvTemplate($headers);
        // Add header (titles)
        $content[] = $headers->toString($template);
        $data = array();

        $getExportSalableProduct = $this->getExportSalableProduct();
        $connection = $this->resource->getConnection();
        foreach ($getExportSalableProduct as $exportSalableProduct) {
             if (is_array($exportSalableProduct)) {
                foreach ($exportSalableProduct as $key => $exportSalable) {
                    if($key == 'sku'){
                        $sql = "SELECT type_id FROM `catalog_product_entity` WHERE (type_id = 'simple' OR type_id = 'virtual') AND `sku` ='".$exportSalable."'";
                        $query = $connection->fetchOne($sql);
                        if(!empty($query)){
                            $sql = "SELECT metadata FROM `inventory_reservation` WHERE `sku` ='".$exportSalable."'";
                            $salable = $connection->fetchAll($sql);
                            if(!empty($salable)){
                                $data['salable_quantity'] = $this->getSalableQuantityDataBySku->execute($exportSalable);
                                if(isset($data['salable_quantity'][0])){
                                    $exportSalableProduct['salable_qty'] = '"' . $data['salable_quantity'][0]['qty'] . '"';
                                }
                                $getOrderIds = '';
                                $getOrderIncrids = '';
                                foreach ($salable as $skey => $svalue) {
                                    $metadata = json_decode($svalue['metadata']);
                                    if($metadata->object_id != '' || $metadata->object_id !='none'){
                                        $getOrderIds .= $metadata->object_id.',';
                                        $sql = "SELECT increment_id FROM `sales_order` WHERE `entity_id` ='".$metadata->object_id."'";
                                        $incrementId = $connection->fetchOne($sql);
                                        $getOrderIncrids .= $incrementId.',';
                                    }
                                }
                                if(!empty($getOrderIds)){
                                    $getOrderIds = rtrim($getOrderIds, ',');
                                }
                                if(!empty($getOrderIncrids)){
                                    $getOrderIncrids = rtrim($getOrderIncrids, ',');
                                }
                                $exportSalableProduct['order_id'] = '"' . $getOrderIds . '"';
                                $exportSalableProduct['inrement_id'] = '"' . $getOrderIncrids . '"';

                           }

                        }
                    }
                    
                    $exportSalableProduct[$key] = '"' . $exportSalable . '"';
                }

                if(isset($exportSalableProduct['order_id'])){
                    $content[] = implode(",", $exportSalableProduct);
                }
            }
        }

        $contentAsAString = implode("\n", $content);
        $outputFile = 'exportsalableorderedproduct_' . date('Y-m-d') . '_' . time() . '.csv';
        return $this->fileFactory->create(
            $outputFile,
            $contentAsAString,
            DirectoryList::VAR_DIR
        );
        
        $response['message'] = 'success';
        echo json_encode($response);
    }


    /**
     * Get headers for the selected entities
     *
     * @return \Magento\Framework\DataObject
     */
    private function getHeaders()
    {
        $dataFields = [
            'sku'             => __('SKU'),
            'qty'             => __('Qty'),
            'salable_qty'     => __('Salable Qty'),
            'order_id'        => __('Order Id'),
            'inrement_id'        => __('Increment Id'),

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
    public function getExportSalableProduct()
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT e.sku AS 'sku',
                si.qty AS 'qty'
                FROM catalog_product_entity e
                INNER JOIN cataloginventory_stock_item si ON e.entity_id = si.product_id";

        return $connection->fetchAll($sql);
    }
}