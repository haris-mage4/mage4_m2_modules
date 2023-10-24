<?php

namespace Mage4\ExportOrder\Controller\Adminhtml\Order;

use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class MassExport extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{

    protected $orderManagement;
    protected $orderRepository;
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    public function __construct(
        Context                                         $context,
        Filter                                          $filter,
        CollectionFactory                               $collectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface     $orderRepository,
        OrderManagementInterface                        $orderManagement,
        FileFactory                                     $fileFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    )
    {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderManagement = $orderManagement;
        $this->fileFactory = $fileFactory;
        $this->resultRawFactory = $resultRawFactory;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $file = fopen('php://output', 'w');
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename=order_options' . date('Y-M-d') . '.csv');
        $contents = [];
        $orderinfo = [
            'ID' => 'ID',
            'Purchase Date' => 'Purchase Date',
            'Bill-to Name' => 'Bill-to Name',
            'Ship-to Name' => 'Ship-to Name',
            'Grand Total (Base)' => 'Grand Total (Base)',
            'Grand Total (Purchased)' => 'Grand Total (Purchased)',
            'Status' => 'Status',
            'Shipping Information' => 'Shipping Information',
            'Customer Email' => 'Customer Email',
            'Customer Group' => 'Customer Group',
            'Subtotal' => 'Subtotal',
            'Shipping and Handling' => 'Shipping and Handling',
            'Customer Name' => 'Customer Name',
            'Payment Method' => 'Payment Method',
            'Total Refunded' => 'Total Refunded',
            'Product Name' => 'Product Name',
            'Sku' => 'Sku'
        ];
        foreach ($collection->getItems() as $order) {
            foreach ($order->getAllItems() as $item) {
                $contents[$item->getId()] = [
                    'ID' => $order->getIncrementId(),
                    'Purchase Date' => $order->getCreatedAt(),
                    'Bill-to Name' => $order->getBillingAddress()->getName(),
                    'Ship-to Name' => $order->getShippingAddress()->getName(),
                    'Grand Total (Base)' => $order->getGrandTotal(),
                    'Grand Total (Purchased)' => $order->getGrandTotal(),
                    'Status' => $order->getStatus(),
                    'Shipping Information' => null,
                    'Customer Email' => $order->getCustomerEmail(),
                    'Customer Group' => $order->getCustomerGroupId(),
                    'Subtotal' => $order->getSubtotal(),
                    'Shipping and Handling' => $order->getShippingAmount(),
                    'Customer Name' => $order->getCustomerName(),
                    'Payment Method' => $order->getPayment()->getMethod(),
                    'Total Refunded' => $order->getPayment()->getAmountRefunded(),
                    'Product Name' => $item->getName(),
                    'Sku' => $item->getSku()
                ];
                $options = $item->getProductOptions();
                if (isset($options['options']) && !empty($options['options'])) {
                    foreach ($options['options'] as $option) {
                        $orderinfo[$option['label']] = $option['label'];
                        $contents[$item->getId()][$option['label']] = empty($option['value']) ? 'K' : $option['value'];
                    }
                } elseif (isset($options['attributes_info']) && !empty($options['attributes_info'])) {
                    foreach ($options['attributes_info'] as $opt) {
                        $orderinfo[$opt['label']] = $opt['label'];
                        $contents[$item->getId()][$opt['label']] = empty($opt['value']) ? 'K' : $opt['value'];
                    }
                }

            }

        }

        foreach ($contents as $k => $content) {
            $diff = array_map(function ($v) {
                return null;
            }, array_diff_key($orderinfo, $content));

            $contents[$k] = array_merge($content, $diff);
            $contents[$k] = array_replace($orderinfo, $contents[$k]);;
        }
        fputcsv($file, array_keys(reset($contents)), ',', '"', '');
        foreach ($contents as $content) {
            fputcsv($file, $content, ',', '"', '');
        }
    }
}
