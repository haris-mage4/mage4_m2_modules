<?php
namespace InformaticsCommerce\TaxExempt\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use  Magento\Sales\Model\OrderRepository;

class CustomColumn extends Column
{
    protected $_orderRepository;
    public function __construct(
        OrderRepository $orderRepository,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_orderRepository = $orderRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Perform any necessary custom logic to fetch the value for the custom column
                $item[$this->getData('name')] = $this->getCustomColumnValue($item);
            }
        }
        return $dataSource;
    }

    private function getCustomColumnValue($item)
    {
        // Fetch and return the value for the custom column based on the item data
        $order  = $this->_orderRepository->get($item["entity_id"]);
        return $order->getTaxExemptNumber();
    }

}
