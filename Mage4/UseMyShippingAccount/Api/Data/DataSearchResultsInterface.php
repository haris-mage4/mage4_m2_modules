<?php

namespace InformaticsCommerce\UseMyShippingAccount\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface DataSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
