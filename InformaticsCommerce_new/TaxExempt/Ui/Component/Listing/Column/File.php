<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace InformaticsCommerce\TaxExempt\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Customer\Model\Customer;

class File extends Column
{
    protected  $customerRepository;
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function __construct(Customer $customerRepository, ContextInterface $context, UiComponentFactory $uiComponentFactory, array $components = [], array $data = [])
    {
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                // Perform any necessary custom logic to fetch the value for the custom column
                $item[$this->getData('name')] = $this->getCustomColumnValue($item)['upload_document'];
            }
        }
        return $dataSource;
    }
    public  function getCustomColumnValue($item){
      return  $this->customerRepository->getCollection()->addFieldToFilter('entity_id', ['eq' => $item['entity_id']])->getData()[0];
    }
}
