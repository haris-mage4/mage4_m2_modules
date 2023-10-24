<?php

namespace Mage4\CsvUpload\Api\Data;

/**
 * Interface ImportSearchResultsInterface
 */
interface ImportSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Import list.
     * @return \Mage4\CsvUpload\Api\Data\ImportInterface[]
     */
    public function getItems();

    /**
     * Set sku list.
     * @param \Mage4\CsvUpload\Api\Data\ImportInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
