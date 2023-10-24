<?php

namespace InformaticsCommerce\RequestForQuote\Block\Request\Quote;

use InformaticsCommerce\RequestForQuote\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Customer\Model\Customer;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 *
 */
class Product extends Template
{
    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var Configurable
     */
    protected $_configurableProduct;

    /**
     * @param Data $helper
     * @param Context $context
     * @param array $data
     */
    public function __construct(Configurable $configurableProduct, Data $helper, Context $context, array $data = [])
    {
        $this->_helper = $helper;
        $this->_configurableProduct = $configurableProduct;
        return parent::__construct($context, $data);
    }

    /**
     * @return mixed|null
     */
    public function getProduct()
    {
        return $this->_helper->getCurrentProduct();
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->_helper->getCustomer();
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getConfigurableProduct($product)
    {
        return $this->_configurableProduct->getConfigurableAttributesAsArray($product);
    }

    /**
     * @return string
     */
    public function getProductMediaPath(){
       return $this->_helper->getProductMediaPath();
    }
      public function getImagePlaceHolder(){
        return $this->_helper->getImagePlaceHolder();
    }
}
