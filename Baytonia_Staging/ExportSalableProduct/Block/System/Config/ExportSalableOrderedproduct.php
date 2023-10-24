<?php

namespace Baytonia\ExportSalableProduct\Block\System\Config;
 
class ExportSalableOrderedproduct extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Baytonia_ExportSalableProduct::system/config/exportsalableorderedproduct.phtml';
 
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
 
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
 
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
 
    public function getAjaxUrl()
    {
        return $this->getUrl('exportsalable/export/orderedproducts');
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
            )->setData(
            [
                'id' => 'exportbtnsalableordered',
                'label' => __('Export Salable Ordered Products'),
            ]
        );
        return $button->toHtml();
    }
}