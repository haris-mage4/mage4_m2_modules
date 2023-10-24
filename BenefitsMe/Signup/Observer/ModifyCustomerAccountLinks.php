<?php
namespace BenefitsMe\Signup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class ModifyCustomerAccountLinks implements ObserverInterface
{
    public function execute(Observer $observer)
    {
	    $links = $observer->getEvent()->getAccountNavigation();

	    debug($links, 1);
	    die();
	    /*
        $links->removeLinkByName('account-subscription');
        $links->removeLinkByName('downloadable_products');
	$links->addLink('custom_link', 'Custom Link', 'custom-url.html');
	     */
    }
}
