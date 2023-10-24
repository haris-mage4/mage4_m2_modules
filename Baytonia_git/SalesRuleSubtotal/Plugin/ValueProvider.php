<?php

namespace Baytonia\SalesRuleSubtotal\Plugin;

use Magento\SalesRule\Model\Rule\Metadata\ValueProvider as SalesRuleValueProvider;

class ValueProvider
{
    /**
     * @var \Baytonia\SalesRuleSubtotal\Helper\Data
     */
    private $rulesDataHelper;

    public function __construct(
        \Baytonia\SalesRuleSubtotal\Helper\Data $rulesDataHelper
    ) {
        $this->rulesDataHelper = $rulesDataHelper;
    }

    public function afterGetMetadataValues(
        SalesRuleValueProvider $subject,
        $result
    ) {
        $actions = &$result['actions']['children']['simple_action']['arguments']['data']['config']['options'];
        $actions = array_merge($actions, $this->rulesDataHelper->getDiscountTypes());
        return $result;
    }
}
