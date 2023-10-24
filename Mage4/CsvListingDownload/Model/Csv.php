<?php



namespace Mage4\CsvListingDownload\Model;


class Csv extends \Magento\Framework\Model\AbstractModel
{

    protected $_csvCollectionFactory;


    protected $_storeViewId = null;


    protected $_csvFactory;


    protected $_formFieldHtmlIdPrefix = 'page_';


    protected $_storeManager;


    protected $_monolog;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Mage4\CsvListingDownload\Model\ResourceModel\Csv $resource,
        \Mage4\CsvListingDownload\Model\ResourceModel\Csv\Collection $resourceCollection,
        \Mage4\CsvListingDownload\Model\CsvFactory $csvFactory,

        \Mage4\CsvListingDownload\Model\ResourceModel\Csv\CollectionFactory $csvCollectionFactory,

        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Logger\Monolog $monolog
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection
        );
        $this->_csvFactory = $csvFactory;


        $this->_storeManager = $storeManager;
        $this->_csvCollectionFactory = $csvCollectionFactory;

        $this->_monolog = $monolog;

        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }




}
