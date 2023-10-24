<?php

namespace Baytonia\ExportSalableProduct\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;

class Odoocustomer extends \Magento\Backend\App\Action
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
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        DataObjectFactory $dataObjectFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        $this->fileFactory   = $fileFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->resource = $resource;
        $this->response = $response;
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

        $getExportOdooCustomer = $this->getExportOdooCustomer();
        foreach ($getExportOdooCustomer as $exportOdooCustomer) {
             if (is_array($exportOdooCustomer)) {
                foreach ($exportOdooCustomer as $key => $exportSalable) {
                    $exportOdooCustomer[$key] = '"' . $exportSalable . '"';
                }

                $content[] = implode(",", $exportOdooCustomer);
            }
           
        }
        $contentAsAString = implode("\n", $content);
        $outputFile = 'exportodoocustomers_' . date('Y-m-d') . '_' . time() . '.csv';
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
            'email'           => __('Customer Email'),
            'magento_id'      => __('Magento Id'),
            'odoo_id'         => __('Odoo Id'),
            'address_id'      => __('Address Id'),
            'created_at'      => __('Created At'),
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
    public function getExportOdooCustomer()
    {
        $connection = $this->resource->getConnection();
        $sql = "SELECT email,magento_id,odoo_id,address_id,odoomagentoconnect_customer.created_at FROM odoomagentoconnect_customer
                LEFT JOIN customer_entity ON customer_entity.entity_id = odoomagentoconnect_customer.magento_id";

        return $connection->fetchAll($sql);
    }
}