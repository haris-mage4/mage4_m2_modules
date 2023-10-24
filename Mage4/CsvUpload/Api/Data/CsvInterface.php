<?php

namespace Mage4\CsvUpload\Api\Data;

/**
 * Interface CsvInterface
 */
interface CsvInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PROCESSED = 'processed';
    const UPLOADED_AT = 'uploaded_at';
    const FILENAME = 'filename';
    const CSV_ID = 'csv_id';

    /**
     * Get csv_id
     * @return string|null
     */
    public function getCsvId();

    /**
     * Set csv_id
     * @param string $csvId
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setCsvId($csvId);

    /**
     * Get filename
     * @return string|null
     */
    public function getFilename();

    /**
     * Set filename
     * @param string $filename
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setFilename($filename);

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Mage4\CsvUpload\Api\Data\CsvExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     * @param \Mage4\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Mage4\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
    );

    /**
     * Get uploaded_at
     * @return string|null
     */
    public function getUploadedAt();

    /**
     * Set uploaded_at
     * @param string $uploadedAt
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setUploadedAt($uploadedAt);

    /**
     * Get processed
     * @return string|null
     */
    public function getProcessed();

    /**
     * Set processed
     * @param string $processed
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setProcessed($processed);
}
