<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Baytonia\OdoomagentoconnectFix\Model\Product\Type;

/**
 * Simple product type implementation
 */
class Simple extends \Magento\Catalog\Model\Product\Type\AbstractType
{
    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
    }
     /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    public function getConfigurableAttributeCollection(\Magento\Catalog\Model\Product $product) {
     return false;
   }
}
