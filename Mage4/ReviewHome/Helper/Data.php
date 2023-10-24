<?php
namespace Mage4\ReviewHome\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Checkout\Model\Session;
use Magento\Customer\Api\AddressRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var \Magento\Tax\Api\Data\TaxClassInterfaceFactory
     */
    protected $storeManager;

    /**
     * constructor.
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface                   $storeManager
    )
    {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->storeManager = $storeManager;
    }

    public function getAllReviews()
    {
        $reviewsCollection = $this->reviewCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getStoreId())
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder();
        return $reviewsCollection;
    }
}

?>
