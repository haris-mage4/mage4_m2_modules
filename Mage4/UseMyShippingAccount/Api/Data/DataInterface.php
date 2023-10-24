<?php

namespace InformaticsCommerce\UseMyShippingAccount\Api\Data;

interface DataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const OPTION_ID             = 'option_id';
    const OPTION_LABEL          = 'option_label';
    const OPTION_CODE           = 'option_code';
    const OPTION_INPUT_TYPE     = 'option_input_type';
    const OPTION_VALUES         = 'option_values';
    const IS_REQUIRED           = 'is_required';
    const APPLY_TO              = 'apply_to';
    const OPTION_DEFAULT_VALUE  = 'option_default_value';
    const SORT_ORDER            = 'sort_order';



    /**
     * Get ID
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set ID
     *
     * @param $optionId
     * @return DataInterface
     */
    public function setOptionId($optionId);

    /**
     * Get $optionLabel
     *
     * @return string
     */
    public function getOptionLabel();

    /**
     * Set $optionLabel
     *
     * @param $optionLabel
     * @return mixed
     */
    public function setOptionLabel($optionLabel);

    /**
     * Get $optionCode
     *
     * @return string
     */
    public function getOptionCode();

    /**
     * Set $optionCode
     *
     * @param $optionCode
     * @return mixed
     */
    public function setOptionCode($optionCode);

    /**
     * Get $optionInputType
     *
     * @return string
     */
    public function getOptionInputType();

    /**
     * Set $optionInputType
     *
     * @param $optionInputType
     * @return mixed
     */
    public function setOptionInputType($optionInputType);

    /**
     * Get $optionvalues
     *
     * @param $optionvalues
     * @return mixed
     */
    public  function getOptionValues();

    /**
     * Set $optionvalues
     *
     * @param $optionInputType
     * @return mixed
     */
    public  function setOptionValues($optionvalues);


    /**
     * Get $isRequired
     *
     * @return string
     */
    public function getIsRequired();

    /**
     * Set $isRequired
     *
     * @param $isRequired
     * @return mixed
     */
    public function setIsRequired($isRequired);

    /**
     * Get $applyTo
     *
     * @return mixed
     */
    public function getApplyTo();

    /**
     * Set $applyTo
     *
     * @param $applyTo
     * @return mixed
     */
    public function setApplyTo($applyTo);

    /**
     * Get $optionDefaultValue
     *
     * @return mixed
     */
    public function getOptionDefaultValue();

    /**
     * Set $optionDefaultValue
     *
     * @param $comment
     * @return mixed
     */
    public function setOptionDefaultValue($optionDefaultValue);

    /**
     * Get Comment
     *
     * @return mixed
     */
    public function getSortOrder();

    /**
     * Set $sortOrder
     *
     * @param $sortOrder
     * @return mixed
     */
    public function setSortOrder($sortOrder);
}
