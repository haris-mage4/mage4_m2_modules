<?php

namespace CapitalOne\Flexpay\Helper;

use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;
use CapitalOne\Flexpay\Model\Adminhtml\System\Config\Country as CountryConfig;

class Country
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CountryConfig
     */
    private $countryConfig;

    /**
     * @var array
     */
    private $countries;

    /**
     * @param CollectionFactory $factory
     * @param CountryConfig $countryConfig
     */
    public function __construct(CollectionFactory $factory, CountryConfig $countryConfig)
    {
        $this->collectionFactory = $factory;
        $this->countryConfig = $countryConfig;
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCountries(): array
    {
        if (!$this->countries) {
            $this->countries = $this->collectionFactory->create()
                ->addFieldToFilter('country_id', ['nin' => $this->countryConfig->getExcludedCountries()])
                ->loadData()
                ->toOptionArray(false);
        }

        return $this->countries;
    }
}
