<?php

namespace Mage4\AdvanceMatrixPricing\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Mage4\AdvanceMatrixPricing\Logger\Logger;


class Data extends  AbstractHelper{

    protected $_filesystem;
    protected $storeManager;
    protected $data;
    protected $_logger;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        StoreManagerInterface $storeManager,
        Logger $_logger
    )    {
        $this->_filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->_logger = $_logger;
        parent::__construct($context);
    }

    public function getCsv() {
          return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function logDataInLogger($data)
    {
        $this->_logger->info($data);
    }
}
