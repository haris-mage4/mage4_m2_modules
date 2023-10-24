<?php

namespace Mage4\AdvanceTopNavbar\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\Form\FormKey;

class Data extends AbstractHelper
{
    protected $productRepository;
    protected $formKey;

    public function __construct(
        Context           $context,
        ProductRepository $productRepository,
        FormKey           $formKey
    )
    {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->formKey = $formKey;
    }

    public function getProductPriceById($productId)
    {
        try {
            $product = $this->productRepository->getById($productId);
            return $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

}
