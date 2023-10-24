<?php
namespace Baytonia\CustomApis\Model;

use Magezon\LookBook\Helper\Data;
use Magezon\LookBook\Model\ResourceModel\Profile\Collection;

class CMSManagement
{


    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface, \Magento\Store\Model\StoreManagerInterface
        $storeManager, \Magento\Store\Model\StoreRepository $storeRepository, \Baytonia\CustomApis\Helper\Cache $cacheHelper, \Magento\Cms\Model\PageFactory $pageFactory, \Magento\Cms\Model\Template\FilterProvider $filterProvide)
    {
        $this->pageFactory = $pageFactory;
        $this->_cacheHelper = $cacheHelper;
        $this->_filterProvide = $filterProvide;
    }
    
    
    /**
     * {@inheritdoc}
     */
    public function getCMSpages($pageid)
    {
        $storeId = 1;
        $cacheId = $this->_cacheHelper->getId("cms_pages", $storeId,array("page_id"=>$pageid));
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }
        try {
        $page = $this->pageFactory->create()->load($pageid);
        $_returnData["success"] = true;
        $_pageData = $page->getData();
        $_pageData["content"] = $this->_filterProvide->getPageFilter()->filter($page->getContent());
        
        $_returnData["page_data"] = $_pageData;
        }
        catch (\Exception $e) {
            $_returnData["success"] = false;
            $_returnData["message"] = __($e->getMessage());
            $dataTosend = json_encode($ress, JSON_UNESCAPED_UNICODE);
            echo $dataTosend;
            die();
        }
        
        $this->_cacheHelper->save(json_encode($_returnData, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($_returnData, JSON_UNESCAPED_UNICODE);
            die();
        
    }
}
