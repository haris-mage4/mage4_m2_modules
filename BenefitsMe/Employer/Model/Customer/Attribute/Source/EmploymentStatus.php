<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace BenefitsMe\Employer\Model\Customer\Attribute\Source;

class EmploymentStatus extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
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
                ['value' => '1', 'label' => __('Active')],
                ['value' => '2', 'label' => __('Terminated')],
                ['value' => '3', 'label' => __('Leave of Absence (LOA)')]
            ];
        }
        return $this->_options;
    }
}

