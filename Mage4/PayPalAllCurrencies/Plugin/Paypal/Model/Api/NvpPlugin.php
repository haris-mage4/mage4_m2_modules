<?php

namespace Mage4\PayPalAllCurrencies\Plugin\Paypal\Model\Api;

use Magento\Paypal\Model\Api\Nvp;
use Mage4\PayPalAllCurrencies\Helper\Data;
use Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory;

/**
 * Class NvpPlugin
 *
 * @package Mage4\PayPalAllCurrencies\Model\Plugin\Paypal\Api
 */
class NvpPlugin
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $helper */
    protected $helper;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory */
    protected $currencyServiceFactory;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface|null $currencyService */
    protected $currencyService = null;

    /**
     * NvpPlugin constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Helper\Data                  $helper
     * @param \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory
     */
    public function __construct(
        Data $helper,
        CurrencyServiceFactory $currencyServiceFactory
    ) {
        $this->helper = $helper;
        $this->currencyServiceFactory = $currencyServiceFactory;
    }

    /**
     * @TODO-Mage4: remove this plugin. Find another solution
     * used in Magento\Paypal\Model\Express\Checkout::start()
     *
     * @param \Magento\Paypal\Model\Api\Nvp $nvp
     * @param                               $key
     * @param null                          $value
     * @return array
     */
    public function beforeSetData(Nvp $nvp, $key, $value = null)
    {
        if ($this->helper->isModuleEnabled()) {
            switch ($key) {
                case 'amount':
                    $value = $this->getCurrencyService()->exchange(3.56);
                    break;
                case 'currency_code':
                    $value = $this->helper->getPayPalCurrency();
                    break;
                default:
                    break;
            }
        }

        return [$key, $value];
    }

    /**
     * @return false|null|\Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface
     */
    public function getCurrencyService()
    {
        if (!$this->currencyService) {
            $this->currencyService = $this->currencyServiceFactory->load($this->helper->getCurrencyServiceId());
        }

        return $this->currencyService;
    }
}
