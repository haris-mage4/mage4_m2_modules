<?php
namespace Baytonia\Extendedconnector\Plugin\Webkul\Odoomagentoconnect\Model\ResourceModel;

use xmlrpc_client;
use xmlrpcval;
use xmlrpcmsg;

class Order
{
    protected $_extraFee;

    public function __construct(
        \Amasty\Extrafee\Model\ResourceModel\ExtrafeeOrder\Collection $extraFee
    ) {
        $this->_extraFee = $extraFee;
    }
	
     /**
     * @param \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $subject
     * @param callable $proceed
     * @param \Magento\Sales\Model\Order $thisOrder
     * @param $pricelistId
     * @param $erpAddressArray
     * @return array
     */
    public function aroundCreateOdooOrder(
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $subject,
        callable $proceed,
        $thisOrder, $pricelistId, $erpAddressArray
    ) {
        $storeCredit=$thisOrder->getAmstorecreditBaseAmount();

        $odooOrder = [];
        $extraFieldArray = [];
        $odooOrderId = 0;
        $partnerId = $erpAddressArray[0];
        $partnerInvoiceId = $erpAddressArray[1];
        $partnerShippingId = $erpAddressArray[2];
        $mageOrderId = $thisOrder->getId();
        $subject->_session->setExtraFieldArray($extraFieldArray);
        $subject->_eventManager->dispatch('odoo_order_sync_before', ['mage_order_id' => $mageOrderId]);

        $helper = $subject->_connection;
        $helper->getSocketConnect();
        $userId = $helper->getSession()->getUserId();
        $extraFieldArray = $subject->_session->getExtraFieldArray();
        $incrementId = $thisOrder->getIncrementId();
        $client = $helper->getClientConnect();
        $context = $helper->getOdooContext();
        $warehouseId = $subject->_session->getErpWarehouse();
        $orderArray =  [
                    'partner_id'=>new xmlrpcval($partnerId, "int"),
                    'partner_invoice_id'=>new xmlrpcval($partnerInvoiceId, "int"),
                    'partner_shipping_id'=>new xmlrpcval($partnerShippingId, "int"),
                    'pricelist_id'=>new xmlrpcval($pricelistId, "int"),
                    'date_order'=>new xmlrpcval($thisOrder->getCreatedAt(), "string"),
                    'origin'=>new xmlrpcval($incrementId, "string"),
                    'warehouse_id'=>new xmlrpcval($warehouseId, "int"),
                    'ecommerce_channel'=>new xmlrpcval('magento', "string"),
                    'ecommerce_order_id'=>new xmlrpcval($thisOrder->getId(), "int"),
                ];
        $allowSequence = $subject->_scopeConfig->getValue('odoomagentoconnect/order_settings/order_name');
        if ($allowSequence) {
            $orderArray['name'] = new xmlrpcval($incrementId, "string");
        }
        /* Adding Shipping Information*/
        if ($thisOrder->getShippingMethod()) {
            $shippingMethod = $thisOrder->getShippingMethod();
            $shippingCode = explode('_', $shippingMethod);
            if ($shippingCode) {
                $shippingCode = $shippingCode[0];
                $erpCarrierId =  $subject->_carrierMapping
                                    ->checkSpecificCarrier($shippingCode);
                if ($erpCarrierId > 0) {
                    $orderArray['carrier_id'] = new xmlrpcval($erpCarrierId, "int");
                }
            }
        }
        /* Adding Payment Information*/
        $paymentMethod = $thisOrder->getPayment()->getMethodInstance()->getTitle();
        /*if ($paymentMethod) {
            $paymentInfo = 'Payment Information:- '.$paymentMethod;
            $orderArray['note'] = new xmlrpcval($paymentInfo, "string");
        }*/
        if ($paymentMethod) {
            $paymentInfo = 'Payment Information:- '.$paymentMethod;
           // $paymentInfo = 'Plugin -> Payment Information:- Stage Order -> Odoo (Test 2)'.$paymentMethod;
            $orderArray['note'] = new xmlrpcval($paymentInfo, "string");
            $orderArray['payment_method_name'] = new xmlrpcval($paymentMethod, "string");
        }
        if($storeCredit){
            $orderArray['store_credit'] = new xmlrpcval($storeCredit,"double");
        }
        /* Add extra fee */ 
        // $extraFeeData = $this->_extraFee->addFilterByOrderId($mageOrderId);
        // if($extraFeeData) {
        //     $extraFee = $extraFeeData->getFirstItem()->getTotalAmount();
        //     $orderArray['extra_fee'] = new xmlrpcval($extraFee,"double");
        // }
        /* Adding Extra Fields*/
        foreach ($extraFieldArray as $field => $value) {
            $orderArray[$field]= $value;
        }
        
         /** Adding Promo Code */
        if ($thisOrder->getCouponCode()) {
            $orderArray['coupon_code'] = new xmlrpcval($thisOrder->getCouponCode(), "string");
        }
        
        
        $msg = new xmlrpcmsg('execute');
        $msg->addParam(new xmlrpcval($helper::$odooDb, "string"));
        $msg->addParam(new xmlrpcval($userId, "int"));
        $msg->addParam(new xmlrpcval($helper::$odooPwd, "string"));
        $msg->addParam(new xmlrpcval("wk.skeleton", "string"));
        $msg->addParam(new xmlrpcval("create_order", "string"));
        $msg->addParam(new xmlrpcval($orderArray, "struct"));
        $msg->addParam(new xmlrpcval($context, "struct"));
        $resp = $client->send($msg);
        if ($resp->faultcode()) {
            $error = "Export Error, Order ".$incrementId." >>".$resp->faultString();
            $helper->addError($error);
        } else {
            $response = $resp->value();
            $status = $response->me["struct"]["status"]->me["boolean"];
            if(!$status){
                $statusMessage = $response->me["struct"]["status_message"]->me["string"];
                $error = "Export Error, Order ".$incrementId.", Error:-".$statusMessage;
                $helper->addError($error);
            } else {
                $odooOrderId = $response->me["struct"]["order_id"]->me["int"];
                $odooOrderName = $response->me["struct"]["order_name"]->me["string"];
                array_push($odooOrder, $odooOrderId);
                array_push($odooOrder, $odooOrderName);
            }
        }
        return $odooOrder;
        //Your plugin code
        return [];
    }
}
