<?php

namespace Mage4\CheckoutSteps\Plugin\CustomerData;

use Magento\Checkout\CustomerData\Cart;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
class MiniCartTotals
{
    /**
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var CheckoutHelper
     */
    private $checkoutHelper;
    /**
     * @var Quote
     */
    private $quote;

    /**
     * CartPlugin constructor.
     * @param Session $checkoutSession
     * @param CheckoutHelper $checkoutHelper
     */
    public function __construct(Session $checkoutSession, CheckoutHelper $checkoutHelper)
    {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @param Cart $subject
     * @param array $data
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetSectionData(Cart $subject, array $data)
    {
        $totals = $this->getQuote()->getTotals();
        $data['is_coupon_applied'] = $this->getQuote()->getCouponCode() ? 1 : 0;
        $data['discountAmount'] = $this->checkoutHelper->formatPrice($this->getQuote()->getShippingAddress()->getDiscountAmount());
        return $data;
    }

    /**
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }
}
