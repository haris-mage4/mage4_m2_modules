<?php

namespace Baytonia\CloudflareSXAdapterExtended\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Skynix\CloudflareSXAdapter\Helper\Data;
use Skynix\CloudflareSXAdapter\Helper\Api;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\PageCache\Version;

class Purge extends \Magento\Backend\Controller\Adminhtml\Cache implements HttpGetActionInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Api
     */
    private $api;
    /**
     * @var Version
     */
    private $version;

    /**
     * Purge constructor.
     * @param Action\Context $context
     * @param TypeListInterface $cacheTypeList
     * @param StateInterface $cacheState
     * @param Pool $cacheFrontendPool
     * @param PageFactory $resultPageFactory
     * @param Data $data
     * @param Api $api
     */
    public function __construct(
        Action\Context $context,
        TypeListInterface $cacheTypeList,
        StateInterface $cacheState,
        Pool $cacheFrontendPool,
        PageFactory $resultPageFactory,
        Data $data,
        Api $api,
        Version $version
    ) {
        parent::__construct(
            $context,
            $cacheTypeList,
            $cacheState,
            $cacheFrontendPool,
            $resultPageFactory
        );
        $this->data = $data;
        $this->api = $api;
        $this->version=$version;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $message = null;
        try {
            if ($this->data->getEnable()) {

                $this->api->getAccounts();
                $zones = $this->api->getZones();
                $flush = $this->api->purgeCache($zones);

                if ($flush) {
                    $this->flushCache($this->version);
                    $this->messageManager->addSuccessMessage(__("The Cloudflare cache has been cleaned."));

                }

            } else {

                throw new \Exception('Module not enable!!!');
            }

        } catch (\Exception $e) {

            $this->messageManager->addExceptionMessage($e);
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('adminhtml/cache/index');
    }


    public function flushCache(Version $subject)
    {
        $_types = [
            'config' ,
            'layout',
            'block_html',
            'collections' ,
            'reflection' ,
            'db_ddl' ,
            'compiled_config',
            'eav',
            'customer_notification',
            'config_integration',
            'config_integration_api',
            'config_webservice',
            'translate'
        ];

        foreach ($_types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
