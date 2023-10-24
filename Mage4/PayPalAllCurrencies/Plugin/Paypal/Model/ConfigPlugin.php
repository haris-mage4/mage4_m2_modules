<?php

namespace Mage4\PayPalAllCurrencies\Plugin\Paypal\Model;

use Magento\Paypal\Model\Config;
use Mage4\PayPalAllCurrencies\Helper\Data;

/**
 * Class ConfigPlugin
 *
 * @package Mage4\PayPalAllCurrencies\Model\Plugin\Paypal
 */
class ConfigPlugin
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $helper */
    protected $helper;

    /**
     * ConfigPlugin constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Helper\Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * Check whether specified currency code is supported
     *
     * @param \Magento\Paypal\Model\Config $config
     * @param                              $result
     * @return bool
     */
    public function afterIsCurrencyCodeSupported(Config $config, $result)
    {
        if (!$result && $this->helper->isModuleEnabled()) {
            $result = true;
        }

        return $result;
    }
}
