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

class Exportnullswatchproduct extends \Magento\Backend\App\Action
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
                    $exportSalableProduct[$key] = '"' . $exportSalable . '"';
                }

                $content[] = implode(",", $exportSalableProduct);
            }
           
        }
        $contentAsAString = implode("\n", $content);
        $outputFile = 'exportproductnullswatch_' . date('Y-m-d') . '_' . time() . '.csv';
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
            'color'           => __('Color'),
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
                eaov.value AS 'color'
                FROM catalog_product_entity e
                INNER JOIN catalog_product_entity_varchar v1 ON e.entity_id = v1.entity_id AND v1.store_id = 0 AND v1.attribute_id = 73
                INNER JOIN catalog_product_entity_int i1 ON e.entity_id = i1.entity_id AND v1.store_id = 0 AND i1.attribute_id = 93
                LEFT JOIN eav_attribute_option_value eaov ON i1.value = eaov.option_id
                LEFT JOIN eav_attribute_option_swatch eaos ON eaov.option_id = eaos.option_id where eaos.value is null";

        return $connection->fetchAll($sql);
    }
}