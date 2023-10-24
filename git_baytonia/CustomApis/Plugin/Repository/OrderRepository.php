<?php

namespace Baytonia\CustomApis\Plugin\Repository;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository as ModelOrderRepository;

class OrderRepository
{
    /**
     * @param ModelOrderRepository $orderRepository
     * @param OrderInterface $order
     * @return OrderInterface
     */
    public function afterGet(
        ModelOrderRepository $orderRepository,
        OrderInterface $order
    ) {
        if(isset($order["mp_gift_cards"]) && $this->isJson($order["mp_gift_cards"])){
            $giftCardArr = json_decode($order["mp_gift_cards"]);
            foreach($giftCardArr as $code => $value){
                $giftCardCode = $code;
            }
            $order->setCouponCode($giftCardCode);
        }else{
            if(!$order->getCouponCode() && !isset($order["coupon_code"])) {
                $order->setCouponCode("");
            }
        }
        return $order;
    }

    public function isJson($string) {
       json_decode($string);
       return json_last_error() === JSON_ERROR_NONE;
    }
}