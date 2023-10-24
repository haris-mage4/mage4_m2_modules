<?php

namespace Mage4\CheckoutSteps\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\Session as CheckoutSession;
#use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\View\Element\FormKey;

class Config extends Template
{
    private ?UrlInterface $url;
    private ?CheckoutSession $checkoutSession;
    private ?FormKey $formKey;

    public function __construct(Context $context, UrlInterface $urlInterface, FormKey $formKey, CheckoutSession $checkoutSession, array $data = [])
    {
        parent::__construct($context, $data);
        $this->url = $urlInterface;
        $this->formKey = $formKey;
        $this->checkoutSession = $checkoutSession;
    }

    public function getConfig(): array
    {
        $config = [];
        $quote = $this->checkoutSession->getQuote();
        $config['minicartCouponSubmitUrl'] = $this->url->getRouteUrl('checkoutsteps/cart_ajax/couponPost');
        return [
            'minicartCouponSubmitUrl' => $this->url->getRouteUrl('checkoutsteps/cart_ajax/couponPost'),
            'isCouponApplied' => $quote->getCouponCode() ? 1 : 0,
            'couponCode' => $quote->getCouponCode() ?: null,
            'formKey' => $this->formKey->getFormKey()
        ];
    }
}
