<?php

namespace CapitalOne\Flexpay\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Method\AbstractMethod;

class Environment implements ArrayInterface
{
    const ENVIRONMENT_PRODUCTION = 'production';
    const ENVIRONMENT_SANDBOX = 'sandbox';

    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::ENVIRONMENT_SANDBOX,
                'label' => 'Sandbox',
            ],
            [
                'value' => self::ENVIRONMENT_PRODUCTION,
                'label' => 'Production'
            ]
        ];
    }
}
