<?php

namespace CapitalOne\Flexpay\Model\Adminhtml\Source;

class CcType extends \Magento\Payment\Model\Source\Cctype
{
    /**
     * @inheritDoc
     */
    public function getAllowedTypes(): array
    {
        return ['VI', 'MC', 'AE', 'DI', 'JCB', 'MI', 'DN'];
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        $allowed = $this->getAllowedTypes();
        $options = [];

        foreach ($this->_paymentConfig->getCcTypes() as $code => $name) {
            if (in_array($code, $allowed)) {
                $options[] = ['value' => $code, 'label' => $name];
            }
        }

        return $options;
    }
}
