<?php

namespace InformaticsCommerce\ShippingAddressResidential\Plugin\Model\Order\Pdf;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Pdf\AbstractPdf;

class PrintPdfPlugin
{
    public function beforeInsertOrder(AbstractPdf $subject, &$page, $obj, $putOrderId = true)
    {die('sads');
        /**
         * @var Order $obj
         */
        $top = $subject->y;
        $page->drawText(__('Address Type:'), 400, $top - 15, 'UTF-8');
        if ($obj->getAddressType()){
            $page->drawText(__($obj->getAddressType()), 400, $top - 15, 'UTF-8');
        }else{
            $page->drawText(__('Commercial'), 480, $top - 15, 'UTF-8');
        }
    }
}
