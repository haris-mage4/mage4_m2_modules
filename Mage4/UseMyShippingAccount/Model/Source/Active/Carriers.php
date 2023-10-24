<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model\Source\Active;

use Magento\Framework\Option\ArrayInterface;
use Magento\Shipping\Model\Config;

class Carriers implements ArrayInterface
{
    protected $shipConfig;
    public function __construct(
        Config $shipConfig
    )
    {
        $this->shipConfig = $shipConfig;
    }

    public function toOptionArray()
    {
       $activeCarries =  $this->shipConfig->getActiveCarriers();
       $carries = [];
       foreach ($activeCarries as $activeCarrierCode => $activeCarrier){
           $title = $activeCarrier->getConfigData('title');
           $code = $activeCarrierCode;
           $carries[] = ['value' => $code, 'label' => $title];
       }
       return $carries;
    }
}
