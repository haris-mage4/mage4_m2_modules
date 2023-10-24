<?php

namespace Baytonia\CheckoutRegionProvider\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const COUNTRY_CODE_PATH = 'general/country/default';
    protected $scopeConfig;
    protected $country;
    public function __construct(
        \Magento\Directory\Model\Country $country,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig

    ) {
        $this->country          = $country;
        $this->scopeConfig      = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'getRegionsList'    => $this->getRegionsList()
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(self::COUNTRY_CODE_PATH, ScopeInterface::SCOPE_WEBSITES);
    }


    /**
     * {@inheritdoc}
     */
    public function getRegionsList()
    {
        $countryCode        = 'SA';
        $regionCollection   = $this->country->loadByCode($countryCode)->getRegions();
        $regions            = $regionCollection->loadData()->toOptionArray(false);
        return json_encode($regions);
    }
}
