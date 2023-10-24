<?php
namespace Baytonia\WebkulMobikulApiExtended\Controller\Checkout;

class OrderEmail extends \Webkul\MobikulApi\Controller\Checkout\OrderEmail
{
    public function execute()
    {
        try {
            $this->verifyRequest();
            $environment = $this->emulate->startEnvironmentEmulation($this->storeId);
            try {

                $order = $this->orderFactory->create()->load($this->orderId);
                $order->setCanSendNewEmailFlag(true);
                $order->save();
                $quote = new \Magento\Framework\DataObject();
                if ($this->customerId != 0) $quote = $this->helper->getCustomerQuote($this->customerId);
                if ($this->quoteId != 0) $quote = $this->quoteFactory->create()->setStoreId($this->storeId)->load($this->quoteId);
                if ($order) {
                    try {
                        $this->eventManager->dispatch(
                            "checkout_type_onepage_save_order_after",
                            ["order"=>$order, "quote"=>$quote]
                        );
                        $this->saveMobikulOrder($order);
                        $this->eventManager->dispatch("checkout_submit_all_after", ["order"=>$order, "quote"=>$quote]);
                        $this->savePurchasePointDetail($order);
                        $this->orderEmailSender->send($order);
                        $quote->removeAllItems()->collectTotals()->save();
                        
                        $this->returnArray["message"] = 'Success';
                        return $this->getJsonResponse($this->returnArray);
                    } catch (\Exception $e) {
                        $this->returnArray["message"] = $e->getMessage();
                        return $this->getJsonResponse($this->returnArray);
                    }
                }

            } catch (\Throwable $e) {
                $this->returnArray["message"] = 'Order not exist, Please verify order';
                return $this->getJsonResponse($this->returnArray);
            }
          
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->returnArray["message"] = $e->getMessage();
            return $this->getJsonResponse($this->returnArray);
        }
    }

    public function verifyRequest()
    {
        if ($this->getRequest()->getMethod() == "POST" && $this->wholeData) {
            $this->storeId          = $this->wholeData["storeId"] ?? 1;
            $this->orderId          = $this->wholeData["orderId"] ?? "";
            $this->checkoutMethod   = $this->wholeData["checkoutMethod"] ?? "guest";
            $this->customerToken    = $this->wholeData["customerToken"] ?? "";
            $this->quoteId          = $this->wholeData["quoteId"] ?? "";
            $this->customerId       = $this->helper->getCustomerByToken($this->customerToken) ?? 0;
            $this->purchasePoint = $this->wholeData["purchasePoint"] ?? "web";
            if ($this->customerId > 0) {
                $this->checkoutMethod = "customer";
            }
            if(!$this->quoteId && $this->orderId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("please validate data.")
                );
            }
            if (!$this->customerId && $this->customerToken != "") {
                $this->returnArray["otherError"] = "customerNotExist";
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("Customer you are requesting does not exist.")
                );
            }
        } else {
            throw new \BadMethodCallException(__("Invalid Request"));
        }
    }

    public function saveMobikulOrder($order)
    {
        $customerName  = $order->getCustomerFirstname()." ".$order->getCustomerLastname();
        $this->mobikulOrder->create()
            ->setOrderId($order->getId())
            ->setRealOrderId($order->getRealOrderId())
            ->setStoreId($order->getStoreId())
            ->setCustomerId($order->getCustomerId())
            ->setCustomerName($customerName)
            ->setOrderTotal($order->getGrandTotal())
            ->setCreatedAt($order->getCreatedAt())
            ->save();
    }

    public function savePurchasePointDetail($order)
    {
        $purchasePoint = $this->orderPurchaseFactory->create();
        $purchasePoint->setIncrementId($order->getId());
        $purchasePoint->setOrderId($order->getIncrementId());
        $purchasePoint->setPurchasePoint($this->purchasePoint);
        $purchasePoint->save();
    }
   
}
