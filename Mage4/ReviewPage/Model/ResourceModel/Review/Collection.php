<?php

namespace Mage4\ReviewPage\Model\ResourceModel\Review;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    public function addProductStatusFilter()
    {
        $this->getSelect()
            ->joinLeft('catalog_product_entity', 'main_table.entity_pk_value = catalog_product_entity.entity_id', [])
            ->joinLeft('catalog_product_entity_int', 'catalog_product_entity_int.attribute_id = 97 AND catalog_product_entity_int.store_id = 0 AND catalog_product_entity_int.entity_id = catalog_product_entity.entity_id', ['status_id' => 'catalog_product_entity_int.value'])
            ->where('catalog_product_entity_int.value <> 2');

        return $this;
    }
}
