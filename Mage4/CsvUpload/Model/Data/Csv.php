<?php

namespace Mage4\CsvUpload\Model\Data;

use Mage4\CsvUpload\Api\Data\CsvInterface;

/**
 * Mage4CsvUpload Csv class
 */
class Csv extends \Magento\Framework\Api\AbstractExtensibleObject implements CsvInterface
{

    /**
     * Get csv_id
     * @return string|null
     */
    public function getCsvId()
    {
        return $this->_get(self::CSV_ID);
    }

    /**
     * Set csv_id
     * @param string $csvId
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setCsvId($csvId)
    {
        return $this->setData(self::CSV_ID, $csvId);
    }

    /**
     * Get filename
     * @return string|null
     */
    public function getFilename()
    {
        return $this->_get(self::FILENAME);
    }

    /**
     * Set filename
     * @param string $filename
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setFilename($filename)
    {
        return $this->setData(self::FILENAME, $filename);
    }

    /**
     * Retrieve existing extension attributes object or create a new one.
     * @return \Mage4\CsvUpload\Api\Data\CsvExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * Set an extension attributes object.
     * @param \Mage4\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Mage4\CsvUpload\Api\Data\CsvExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    /**
     * Get uploaded_at
     * @return string|null
     */
    public function getUploadedAt()
    {
        return $this->_get(self::UPLOADED_AT);
    }

    /**
     * Set uploaded_at
     * @param string $uploadedAt
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setUploadedAt($uploadedAt)
    {
        return $this->setData(self::UPLOADED_AT, $uploadedAt);
    }

    /**
     * Get processed
     * @return string|null
     */
    public function getProcessed()
    {
        return $this->_get(self::PROCESSED);
    }

    /**
     * Set processed
     * @param string $processed
     * @return \Mage4\CsvUpload\Api\Data\CsvInterface
     */
    public function setProcessed($processed)
    {
        return $this->setData(self::PROCESSED, $processed);
    }
}
