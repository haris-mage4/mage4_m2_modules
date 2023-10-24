<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model;

use Magento\Framework\Model\AbstractModel;
use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterface;

class Data extends AbstractModel implements DataInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'ic_shippingoptions_adminhtml';

    /**
     * Initialise resource model
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init('InformaticsCommerce\UseMyShippingAccount\Model\ResourceModel\Data');
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getOptionId()];
    }

    /**
     * Get Id
     *
     * @return string
     */
    public function getOptionId()
    {
        return $this->getData(DataInterface::OPTION_ID);
    }

    /**
     * Set Id
     *
     * @param $optionId
     * @return $this
     */
    public function setOptionId($optionId)
    {
        return $this->setData(DataInterface::OPTION_ID, $optionId);
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getOptionLabel()
    {
        return $this->getData(DataInterface::OPTION_LABEL);
    }

    /**
     * Set firstname
     *
     * @param $optionLabel
     * @return $this
     */
    public function setOptionLabel($optionLabel)
    {
        return $this->setData(DataInterface::OPTION_LABEL, $optionLabel);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getOptionCode()
    {
        return $this->getData(DataInterface::OPTION_CODE);
    }

    /**
     * Set last name
     *
     * @param $optionCode
     * @return $this
     */
    public function setOptionCode($optionCode)
    {
        return $this->setData(DataInterface::OPTION_CODE, $optionCode);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getOptionInputType()
    {
        return $this->getData(DataInterface::OPTION_INPUT_TYPE);
    }

    /**
     * Set email
     *
     * @param $optionInputType
     * @return $this
     */
    public function setOptionInputType($optionInputType)
    {
        return $this->setData(DataInterface::OPTION_INPUT_TYPE, $optionInputType);
    }
    /**
     * Get email
     *
     * @return string
     */
    public function getOptionValues()
    {
        return $this->getData(DataInterface::OPTION_VALUES);
    }

    /**
     * Set email
     *
     * @param $optionValues
     * @return $this
     */
    public function setOptionValues($optionValues)
    {
        return $this->setData(DataInterface::OPTION_VALUES, $optionValues);
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getIsRequired()
    {
        return $this->getData(DataInterface::IS_REQUIRED);
    }

    /**
     * Set phone
     *
     * @param $isRequired
     * @return $this
     */
    public function setIsRequired($isRequired)
    {
        return $this->setData(DataInterface::IS_REQUIRED, $isRequired);
    }

    public function getApplyTo()
    {
        return $this->getData(DataInterface::APPLY_TO);
    }

    public function setApplyTo($applyTo)
    {
        return $this->setData(DataInterface::APPLY_TO, $applyTo);
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getOptionDefaultValue()
    {
        return $this->getData(DataInterface::OPTION_DEFAULT_VALUE);
    }

    /**
     * Set address
     *
     * @param $optionDefaultValue
     * @return $this
     */
    public function setOptionDefaultValue($optionDefaultValue)
    {
        return $this->setData(DataInterface::OPTION_DEFAULT_VALUE, $optionDefaultValue);
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->getData(DataInterface::SORT_ORDER);
    }

    /**
     * Set comment
     *
     * @param $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        return $this->setData(DataInterface::SORT_ORDER, $sortOrder);
    }
}
