<?php

namespace Baytonia\SpotiiExtended\Helper;

use Spotii\Spotiipay\Model\Config\Container\SpotiiApiConfigInterface;
use Spotii\Spotiipay\Helper\Data as SpotiiHelper;

class Data extends SpotiiHelper
{
    const SPOTII_LOG_FILE_PATH = '/var/log/spotiipay.log';

    /**
     * @var SpotiiApiConfigInterface
     */
    private $spotiiApiConfig;
    
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param SpotiiApiConfigInterface $spotiiApiConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SpotiiApiConfigInterface $spotiiApiConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $spotiiApiConfig);
        $this->logger = $logger;   
    }


     /**
     * Dump Spotii log actions
     *
     * @param string $msg
     * @return void
     */
    public function logSpotiiActions($data = null)
    {
        $this->logger->debug($data);
    }
}
