<?php

namespace InformaticsCommerce\ShippingAddressResidential\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Quote\Model\QuoteRepository;

class ShippingInformationManagementPlugin
{

    protected $_session;
     protected $_quoteRepository;


    public function __construct(QuoteRepository $quoteFactory, Session $session)
    {
        $this->_quoteRepository = $quoteFactory;
        $this->session = $session;
    }

    public function beforeSaveAddressInformation(ShippingInformationManagement $subject, $cartId, ShippingInformationInterface $addressInformation)
    {
        $shippingAddress = $addressInformation->getShippingAddress();
        $shippingAddressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        $quote =  $this->_quoteRepository->get($cartId);

        if ($shippingAddressExtensionAttributes && $shippingAddressExtensionAttributes->getIsResidential() !== null) {
            $shipmentType = $shippingAddressExtensionAttributes->getIsResidential();
            if ($shipmentType){
                $quote->setAddressType('Residential');
            } else{
                $quote->setAddressType('Commercial');
            }
            $quote->save();
        }
    }
}
