<?php

namespace Mage4\PayPalAllCurrencies\Plugin\Config\Model;

use Magento\Config\Model\Config;
use Mage4\PayPalAllCurrencies\Helper\Data;
use Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory;
use Mage4\PayPalAllCurrencies\Model\RatesFactory;

/**
 * Class ConfigPlugin
 */
class ConfigPlugin
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $_helper */
    protected $_helper;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory */
    protected $currencyServiceFactory;

    /** @var \Mage4\PayPalAllCurrencies\Model\RatesFactory $ratesFactory */
    protected $ratesFactory;

    /**
     * ConfigPlugin constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Helper\Data                  $helper
     * @param \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory
     * @param \Mage4\PayPalAllCurrencies\Model\RatesFactory           $ratesFactory
     */
    public function __construct(
        Data $helper,
        CurrencyServiceFactory $currencyServiceFactory,
        RatesFactory $ratesFactory
    ) {
        $this->_helper = $helper;
        $this->currencyServiceFactory = $currencyServiceFactory;
        $this->ratesFactory = $ratesFactory;
    }

    /**
     * @param Config $config
     * @param Config $result
     * @return Config
     */
    public function afterSave(Config $config, $result)
    {
        if ($config->getSection() === 'Mage4_paypalallcurrencies') {
            /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface $currencyService */
            $currencyService = $this->currencyServiceFactory->load($this->_helper->getCurrencyServiceId());

            /** @var \Mage4\PayPalAllCurrencies\Model\Rates $ratesModel */
            $ratesModel = $this->ratesFactory->create();
            $ratesModel->updateRateFromService($currencyService);
            $ratesModel->save();
        }

        return $result;
    }
}
