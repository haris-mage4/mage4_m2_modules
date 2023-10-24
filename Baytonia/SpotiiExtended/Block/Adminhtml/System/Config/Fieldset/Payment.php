<?php

namespace Baytonia\SpotiiExtended\Block\Adminhtml\System\Config\Fieldset;

/**
 * Fieldset renderer for Spotii solution
 */
class Payment extends \Spotii\Spotiipay\Block\Adminhtml\System\Config\Fieldset\Payment
{
    /**
     * Check whether current payment method is enabled
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isPaymentEnabled($element)
    {
        $groupConfig = $element->getGroup();
        $activityPaths = isset($groupConfig['activity_path']) ? $groupConfig['activity_path'] : [];

        if (!is_array($activityPaths)) {
            $activityPaths = [$activityPaths];
        }

        $isPaymentEnabled = true;
        foreach ($activityPaths as $activityPath) {
            $isPaymentEnabled = $isPaymentEnabled
                || (bool)(string)$this->_backendConfig->getConfigDataValue($activityPath);
        }

        return $isPaymentEnabled;
    }
}
