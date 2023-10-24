<?php

namespace Baytonia\CurrencyFormat\Plugin\Directory;

use Magento\Framework\Locale\Currency as LocaleCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\NumberFormatterFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Locale\ResolverInterface as LocalResolverInterface;

class Currency extends \Magento\Directory\Model\Currency
{

    /**
     * @var LocalResolverInterface
     */
    private $localeResolver;

    /**
     * @var NumberFormatterFactory
     */
    private $numberFormatterFactory;

    /**
     * @var \Magento\Framework\NumberFormatter
     */
    private $numberFormatter;

    /**
     * @var array
     */
    private $numberFormatterCache;

    /**
     * @var Json
     */
    private $serializer;

     /**
     * @param float $price
     * @param array $options
     * @return string
     */
    public function formatTxt($price, $options = [])
    {
        if (!is_numeric($price)) {
            $price = $this->_localeFormat->getNumber($price);
        }
        /**
         * Fix problem with 12 000 000, 1 200 000
         *
         * %f - the argument is treated as a float, and presented as a floating-point number (locale aware).
         * %F - the argument is treated as a float, and presented as a floating-point number (non-locale aware).
         */
        $price = sprintf("%F", $price);

        return $this->_localeCurrency->getCurrency($this->getCode())->toCurrency($price, $options);
    }

    /**
     * Convert Numbers.
     *
     * @param string $number
     * @return string
     */
    public function convertArabicNumbers($string) {
        return strtr($string, array('۰'=>'0', '۱'=>'1', '۲'=>'2', '۳'=>'3', '۴'=>'4', '۵'=>'5', '۶'=>'6', '۷'=>'7', '۸'=>'8', '۹'=>'9', '٠'=>'0', '١'=>'1', '٢'=>'2', '٣'=>'3', '٤'=>'4', '٥'=>'5', '٦'=>'6', '٧'=>'7', '٨'=>'8', '٩'=>'9'));
    }

    /**
     * Check if to use Intl.NumberFormatter to format currency.
     *
     * @param array $options
     * @return bool
     */
    private function canUseNumberFormatter(array $options): bool
    {
        $allowedOptions = [
            'precision',
            LocaleCurrency::CURRENCY_OPTION_DISPLAY,
            LocaleCurrency::CURRENCY_OPTION_SYMBOL
        ];

        if (!empty(array_diff(array_keys($options), $allowedOptions))) {
            return false;
        }

        if (array_key_exists('display', $options)
            && $options['display'] !== \Magento\Framework\Currency::NO_SYMBOL
            && $options['display'] !== \Magento\Framework\Currency::USE_SYMBOL
        ) {
            return false;
        }

        return true;
    }

    /**
     * Format currency.
     *
     * @param string $price
     * @param array $options
     * @return string
     */
    private function formatCurrency(string $price, array $options): string
    {
        $customerOptions = new \Magento\Framework\DataObject([]);

        $this->_eventManager->dispatch(
            'currency_display_options_forming',
            ['currency_options' => $customerOptions, 'base_code' => $this->getCode()]
        );
        $options += $customerOptions->toArray();

        $this->numberFormatter = $this->getNumberFormatter($options);

        $formattedCurrency = $this->numberFormatter->formatCurrency(
            $price, $this->getCode() ?? $this->numberFormatter->getTextAttribute(\NumberFormatter::CURRENCY_CODE)
        );

        if (array_key_exists(LocaleCurrency::CURRENCY_OPTION_SYMBOL, $options)) {
            // remove only one non-breaking space from custom currency symbol to allow custom NBSP in currency symbol
            $formattedCurrency = preg_replace('/ /u', '', $formattedCurrency, 1);
        }

        if ((array_key_exists(LocaleCurrency::CURRENCY_OPTION_DISPLAY, $options)
                && $options[LocaleCurrency::CURRENCY_OPTION_DISPLAY] === \Magento\Framework\Currency::NO_SYMBOL)) {
            $formattedCurrency = str_replace(' ', '', $formattedCurrency);
        }

        return preg_replace('/^\s+|\s+$/u', '', $formattedCurrency);
    }

    /**
     * Get NumberFormatter object from cache.
     *
     * @param array $options
     * @return \Magento\Framework\NumberFormatter
     */
    private function getNumberFormatter(array $options): \Magento\Framework\NumberFormatter
    {
        $this->localeResolver = ObjectManager::getInstance()->get(LocalResolverInterface::class);
        $this->numberFormatterFactory = ObjectManager::getInstance()->get(NumberFormatterFactory::class);
        $this->serializer = ObjectManager::getInstance()->get(Json::class);
        $key = 'currency_' . md5($this->localeResolver->getLocale() . $this->serializer->serialize($options));
        if (!isset($this->numberFormatterCache[$key])) {
            $this->numberFormatter = $this->numberFormatterFactory->create(
                ['locale' => $this->localeResolver->getLocale(), 'style' => \NumberFormatter::CURRENCY]
            );

            $this->setOptions($options);
            $this->numberFormatterCache[$key] = $this->numberFormatter;
        }

        return $this->numberFormatterCache[$key];
    }
}