<?php

namespace CapitalOne\Flexpay\Helper;

use CapitalOne\Flexpay\Model\Adminhtml\Source\CcType as CcTypeSource;

class CcType
{
    /**
     * All possible credit card types
     *
     * @var array
     */
    private $ccTypes = [];

    /**
     * @var CcTypeSource
     */
    private $ccTypeSource;

    /**
     * @param CcTypeSource $ccTypeSource
     */
    public function __construct(CcTypeSource $ccTypeSource)
    {
        $this->ccTypeSource = $ccTypeSource;
    }

    /**
     * All possible credit card types
     *
     * @return array
     */
    public function getCcTypes(): array
    {
        if (!$this->ccTypes) {
            $this->ccTypes = $this->ccTypeSource->toOptionArray();
        }
        return $this->ccTypes;
    }
}
