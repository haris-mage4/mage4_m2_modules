<?php

namespace Baytonia\Productsync\Observer;

use Magento\Framework\Event\ObserverInterface;

class ProductImport implements ObserverInterface
{
    protected $_options;
    protected $logger;
    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Catalog\Model\Product\Option $options,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Template $templateMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Template $templateModel,
        \Webkul\Odoomagentoconnect\Model\Product $productMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Product $productModel,
        \Magento\Catalog\Model\Product $catalogModel,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->logger               = $loggerInterface;
        $this->_options = $options;
        $this->_connection = $connection;
        $this->_templateMapping = $templateMapping;
        $this->_templateModel = $templateModel;
        $this->_productMapping = $productMapping;
        $this->_productModel = $productModel;
        $this->_catalogModel = $catalogModel;
        $this->_productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {
            $helper = $this->_connection;
            $helper->getSocketConnect();
            $countNonSyncProduct = 0;
            $countSyncProduct = 0;
            $alreadySyncProduct = 0;
            $countUpdateProduct = 0;
            $countNonUpdateProduct = 0;
            $errorMessage = '';
            $errorUpdateMessage = '';

            $bunch = $observer->getBunch();
            foreach($bunch as $product) {
                $sku       = $product['sku'];
                $product    = $this->_productRepository->get($sku);
                $productId  = $product->getId();
                if ($product->getTypeId() == "configurable") {
                    $mapping = $this->_templateMapping->getCollection()
                                                 ->addFieldToFilter('magento_id', ['eq'=>$productId]);
                    $templateObj = $this->_templateModel;
                    if ($mapping->getSize() == 0) {
                        $response = $templateObj->exportSpecificConfigurable($productId);
                        if ($response['odoo_id'] > 0) {
                            $erpTemplateId = $response['odoo_id'];
                            $templateObj->syncConfigChildProducts($productId, $erpTemplateId);
                        } else {
                        }
                    } else {
                        foreach ($mapping as $mageObj) {
                                $response = $templateObj->updateConfigurableProduct($mageObj);
                                if ($response['odoo_id'] > 0) {
                                } else {
                                }
                        }
                    }
                } else {
                    
                    $mapping = $this->_productMapping->getCollection()
                                                 ->addFieldToFilter('magento_id', ['eq'=>$productId]);

                    $productObj = $this->_productModel;
                    if ($mapping->getSize() == 0) {
                        $response = $productObj->createSpecificProduct($productId);
                        if ($response['odoo_id'] > 0) {
                        } else {
                        }
                    } else {

                        foreach ($mapping as $mageObj) {

                                $response = $productObj->updateNormalProduct($mageObj);
                                if ($response['odoo_id'] > 0) {
                                } else {
                                }
                        }
                    }
                }
            }
            } catch (\Execption $e) {
            echo $e->getMessage(); 
        }
    }
}