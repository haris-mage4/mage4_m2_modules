<?php

namespace Mage4\CsvUpload\Api;

/**
 * Interface CsvRepositoryInterface
 */
interface CsvRepositoryInterface
{

    /**
     * Save Csv
     * @param \Mage4\CsvUpload\Api\Data\CsvInterface $csv
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Mage4\CsvUpload\Api\Data\CsvInterface $csv
    );

    /**
     * Retrieve Csv
     * @param string $csvId
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($csvId);

    /**
     * Retrieve Csv matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Mage4\CsvUpload\Api\Data\CsvSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete Csv
     * @param \Mage4\CsvUpload\Api\Data\CsvInterface $csv
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Mage4\CsvUpload\Api\Data\CsvInterface $csv
    );

    /**
     * Delete Csv by ID
     * @param string $csvId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($csvId);
}
