<?php  declare(strict_types=1);

namespace Baytonia\SalesRuleSubtotal\Plugin\Promo\Quote\Action;

class Details
{

    public function afterGetTemplate(
        \Mexbs\ApBase\Block\Adminhtml\Promo\Quote\Action\Details $subject,
        $result
    ) {
        return 'Baytonia_SalesRuleSubtotal::promo/action/details.phtml';
    }
}