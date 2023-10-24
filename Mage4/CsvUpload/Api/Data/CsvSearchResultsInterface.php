<?php

namespace Mage4\CsvUpload\Api\Data;

/**
 * Interface CsvSearchResultsInterface
 */
interface CsvSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get Csv list.
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface[]
     */
    public function getItems();

    /**
     * Set filename list.
     * @param \Mage4\CsvUpload\Api\Data\CsvInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
