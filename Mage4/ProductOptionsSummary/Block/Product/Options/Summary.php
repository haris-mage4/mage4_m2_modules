<?php

namespace Mage4\ProductOptionsSummary\Block\Product\Options;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Smartwave\Porto\Block\Template;

class Summary extends Template {
    public function __construct(
        Context $context, Registry $coreRegistry, array $data = []
    )    {
        parent::__construct($context, $coreRegistry, $data);
    }
}
