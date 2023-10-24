<?php

namespace Mage4\PayPalAllCurrencies\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class TestCurrencyConverter
 *
 * @package Mage4\PayPalAllCurrencies\Block\System\Config
 */
class TestCurrencyConverter extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Mage4_PayPalAllCurrencies::system/config/testcurrencyconverter.phtml';

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('paypalallcurrencies/system_config/testcurrencyconverter');
    }
}
