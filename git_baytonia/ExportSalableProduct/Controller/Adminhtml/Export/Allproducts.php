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

class Allproducts extends \Magento\Backend\App\Action
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
        foreach ($getExportSalableProduct as $exportSalableProduct) {
             if (is_array($exportSalableProduct)) {
                foreach ($exportSalableProduct as $key => $exportSalable) {
                    if($key == 'enable'){
                        if($exportSalable == 2){
                            $exportSalable = 'enabled';
                        }else{
                            $exportSalable = 'disabled';
                        }
                    }
                    if($key == 'sku' && ($exportSalableProduct['type'] == 'simple' || $exportSalableProduct['type'] == 'virtual')){
                       $data['salable_quantity'] = $this->getSalableQuantityDataBySku->execute($exportSalable);
                        if(isset($data['salable_quantity'][0])){
                            $exportSalableProduct['salable_qty'] = '"' . $data['salable_quantity'][0]['qty'] . '"';
                        }
                    }

                    $exportSalableProduct[$key] = '"' . $exportSalable . '"';
                }

                $content[] = implode(",", $exportSalableProduct);
            }
           
        }
        $contentAsAString = implode("\n", $content);
        $outputFile = 'exportproduct_' . date('Y-m-d') . '_' . time() . '.csv';
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
            'product_name'    => __('Name'),
            'type'            => __('Type'),
            'backorder'       => __('Is Backorder'),
            'instock'         => __('Instock'),
            'enable'          => __('Enable'),
            'qty'             => __('Qty'),
            'salable_qty'     => __('Salable Qty'),

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
                v1.value AS 'product_name',
                e.type_id AS type,
                si.backorders AS backorder,
                si.is_in_stock AS instock,
                e1.value AS enable,
                si.qty AS 'qty'
                FROM catalog_product_entity e
                INNER JOIN cataloginventory_stock_item si ON e.entity_id = si.product_id 
                INNER JOIN catalog_product_entity_varchar v1 ON e.entity_id = v1.entity_id AND v1.store_id = 0
                AND v1.attribute_id =
                  (SELECT attribute_id
                   FROM eav_attribute
                   WHERE attribute_code = 'name'
                     AND entity_type_id =
                       (SELECT entity_type_id
                        FROM eav_entity_type
                        WHERE entity_type_code = 'catalog_product'))
                INNER JOIN catalog_product_entity_int e1 ON e.entity_id = e1.entity_id AND e1.store_id = 0
                AND e1.attribute_id =
                  (SELECT attribute_id
                   FROM eav_attribute
                   WHERE attribute_code = 'status')";

        return $connection->fetchAll($sql);
    }
}