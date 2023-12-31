<?php

namespace Mage4\CsvUpload\Model;

use Magento\Framework\Api\DataObjectHelper;
use Mage4\CsvUpload\Api\Data\CsvInterface;
use Mage4\CsvUpload\Api\Data\CsvInterfaceFactory;

/**
 * Mage4 CsvUpload Csv class
 */
class Csv extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'Mage4_csvupload_csv';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CsvInterfaceFactory
     */
    protected $csvDataFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CsvInterfaceFactory $csvDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Mage4\CsvUpload\Model\ResourceModel\Csv $resource
     * @param \Mage4\CsvUpload\Model\ResourceModel\Csv\Collection $resourceCollection
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CsvInterfaceFactory $csvDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Mage4\CsvUpload\Model\ResourceModel\Csv $resource,
        \Mage4\CsvUpload\Model\ResourceModel\Csv\Collection $resourceCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        array $data = []
    ) {
        $this->csvDataFactory = $csvDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Before save
     */
    public function beforeSave()
    {
        if ($this->isObjectNew()) {
            $this->setProcessed(0);
            $this->setUploadedAt($this->dateTime->gmtDate());
        }
        return parent::beforeSave();
    }

    /**
     * Retrieve csv model with csv data
     * @return CsvInterface
     */
    public function getDataModel()
    {
        $csvData = $this->getData();

        $csvDataObject = $this->csvDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $csvDataObject,
            $csvData,
            CsvInterface::class
        );

        return $csvDataObject;
    }
}
