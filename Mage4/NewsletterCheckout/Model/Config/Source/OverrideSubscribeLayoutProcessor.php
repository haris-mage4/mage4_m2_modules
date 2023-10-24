<?php

namespace Mage4\NewsletterCheckout\Model\Config\Source;

use Mageside\SubscribeAtCheckout\Helper\Config as Helper;
use Mageside\SubscribeAtCheckout\Model\Config\Source\SubscribeLayoutProcessor;


class OverrideSubscribeLayoutProcessor extends  SubscribeLayoutProcessor {

    protected $_helper;

    public function __construct(Helper $helper)
    {
        $this->_helper = $helper;
        parent::__construct($helper);
    }

    public function process($jsLayout){
        $checkbox = $this->_helper->getConfigModule('checkout_subscribe');
        $checked = $checkbox == 2 ? 0 : 1;
        $visible = $checkbox == 3 ? 0 : 1;
        $changeable = $checkbox == 4 ? 0 : 1;
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset'])) {

            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
            ['shippingAddress']['children']['shipping-address-fieldset']['children']['newsletter-subscribe'] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'checkoutLabel' =>
                        $this->_helper->getConfigModule('checkout_label'),
                    'checked' => $checked,
                    'visible' => $visible,
                    'changeable' => $changeable,
                    'template' => 'Mageside_SubscribeAtCheckout/form/element/newsletter-subscribe'
                ],
                'component' => 'Magento_Ui/js/form/form',
                'displayArea' => 'newsletter-subscribe',
            ];
        }
        return $jsLayout;
    }
}
