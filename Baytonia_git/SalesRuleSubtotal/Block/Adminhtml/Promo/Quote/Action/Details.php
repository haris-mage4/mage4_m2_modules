<?php

namespace Baytonia\SalesRuleSubtotal\Block\Adminhtml\Promo\Quote\Action;

class Details
{
    public function afterGetTemplate(
        \Mexbs\ApBase\Block\Adminhtml\Promo\Quote\Action\Details $subject,
        $result
    ) {
        return 'Baytonia_SalesRuleSubtotal::promo/action/details.phtml';
    }

}