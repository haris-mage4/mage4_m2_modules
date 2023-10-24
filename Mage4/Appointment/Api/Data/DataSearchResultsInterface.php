<?php

namespace Mage4\Appointment\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface DataSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get data list.
     *
     * @return \Mage4\Appointment\Api\Data\DataInterface[]
     */
    public function getItems();

    /**
     * Set data list.
     *
     * @param \Mage4\Appointment\Api\Data\DataInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
