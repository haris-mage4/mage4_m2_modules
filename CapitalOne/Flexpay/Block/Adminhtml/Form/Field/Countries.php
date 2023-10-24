<?php

namespace CapitalOne\Flexpay\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use CapitalOne\Flexpay\Helper\Country;
use Magento\Framework\View\Element\Html\Select;

class Countries extends Select
{
    /**
     * @var Country
     */
    private $countryHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Country $countryHelper
     * @param array $data
     */
    public function __construct(Context $context, Country $countryHelper, array $data = [])
    {
        parent::__construct($context, $data);
        $this->countryHelper = $countryHelper;
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->countryHelper->getCountries());
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
