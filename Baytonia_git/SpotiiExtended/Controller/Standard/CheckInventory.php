<?php

namespace Baytonia\SpotiiExtended\Controller\Standard;

use \Magento\Framework\App\ObjectManager;

/**
 * Spotii Helper
 */
class CheckInventory extends \Spotii\Spotiipay\Controller\Standard\CheckInventory
{
    /**
     * Dump Spotii log actions
     *
     * @param string $msg
     * @return void
     */
    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $itemsString = $post['items'];
        $this->spotiiHelper->logSpotiiActions($itemsString);

        $flag = true;
        $json = $this->_jsonHelper->jsonEncode(["isInStock" => $flag]);
        $jsonResult = $this->_resultJsonFactory->create();
        $jsonResult->setData($json);
        return $jsonResult;
    }

    /**
     *
     * @return int
     */
    public function getProductQuantity($sku)
    {
        $resource = ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();

        $catalog_product_entity = $resource->getTableName('catalog_product_entity');
        $cataloginventory_stock_item = $resource->getTableName('cataloginventory_stock_item');
 
        //Initiate Connection
        $connection = $resource->getConnection();
 
        $select = $connection->select()
            ->from(
                ['cep' => $catalog_product_entity],
                ['csi.qty']
            )->join(
            ['csi' => $cataloginventory_stock_item],
            ' cep.entity_id = csi.product_id'
              )->where(
                    "cep.sku = :sku"
            );
        $bind = ['sku'=>$sku];
        $qty = $connection->fetchOne($select, $bind);
 
        return $qty;
    }
}