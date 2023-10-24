<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BenefitsMe\Employer\Model\Customer\Attribute\Source;

class PayFrequency extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __('Weekly')],
                ['value' => '2', 'label' => __('Bi-Weekly')],
                ['value' => '3', 'label' => __('Semi-Monthly')],
                ['value' => '4', 'label' => __('Monthly')]
            ];
        }
        return $this->_options;
    }
}

