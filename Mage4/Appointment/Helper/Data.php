<?php


namespace Mage4\Appointment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
//use Vendor\Amasty\Storelocator\Model\Location;

class Data extends AbstractHelper
{
    protected $request;
    protected $objectManager;

    /**
     * @var Collection | null
     */
    protected $itemCollection;

    public function __construct(
        Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
        $this->request = $request;
        $this->objectManager = $objectManager;
        parent::__construct($context);
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getObjectManager()
    {
        return $this->objectManager;
    }

    public function getStoreLocationCollection()
    {
//         /** @var \Amasty\Storelocator\Model\Location $locator */
//         /** @var \Amasty\Storelocator\Model\Location $locator */
//         $locator = \Magento\Framework\App\ObjectManager::getInstance()->get(\Amasty\Storelocator\Model\Location::class);
//         return $locator->getCollection()->getData();
// /*
//         $collection = $this->_objectManager->get('Amasty\Storelocator\Model\Location')->getCollection();
//         $collection->g
//         echo $collection->getSize();
//         exit;
// */
//         $array = [0,1];
//         return $array;
//         //return $this->request;
    }

}
