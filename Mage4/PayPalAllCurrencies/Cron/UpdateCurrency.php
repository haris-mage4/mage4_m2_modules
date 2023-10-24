<?php

namespace Mage4\PayPalAllCurrencies\Cron;

use Mage4\PayPalAllCurrencies\Helper\Data;
use Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory;
use Mage4\PayPalAllCurrencies\Model\RatesFactory;
use Psr\Log\LoggerInterface;

class UpdateCurrency
{
    /** @var \Mage4\PayPalAllCurrencies\Helper\Data $helper */
    protected $helper;

    /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory */
    protected $currencyServiceFactory;

    /** @var \Mage4\PayPalAllCurrencies\Model\RatesFactory $ratesFactory */
    protected $ratesFactory;

    /** @var \Psr\Log\LoggerInterface $logger */
    protected $logger;

    /**
     * ConfigPlugin constructor.
     *
     * @param \Mage4\PayPalAllCurrencies\Helper\Data                  $helper
     * @param \Mage4\PayPalAllCurrencies\Model\CurrencyServiceFactory $currencyServiceFactory
     * @param \Mage4\PayPalAllCurrencies\Model\RatesFactory           $ratesFactory
     * @param \Psr\Log\LoggerInterface                                   $logger
     */
    public function __construct(
        Data $helper,
        CurrencyServiceFactory $currencyServiceFactory,
        RatesFactory $ratesFactory,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->currencyServiceFactory = $currencyServiceFactory;
        $this->ratesFactory = $ratesFactory;
        $this->logger = $logger;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        /** @var \Mage4\PayPalAllCurrencies\Model\CurrencyService\CurrencyServiceInterface $currencyService */
        $currencyService = $this->currencyServiceFactory->load($this->helper->getCurrencyServiceId());

        /** @var \Mage4\PayPalAllCurrencies\Model\Rates $ratesModel */
        $ratesModel = $this->ratesFactory->create();
        $ratesModel->updateRateFromService($currencyService);
        $ratesModel->save();
        $this->logger->info('Rate is updated');

        return $this;
    }
}
