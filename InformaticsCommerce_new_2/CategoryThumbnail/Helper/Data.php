<?php

namespace InformaticsCommerce\CategoryThumbnail\Helper;

use Magento\Framework\ObjectManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $storeManager;
    protected $objectManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ObjectManagerInterface $objectManagerInterface
    ) {
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManagerInterface;
        parent::__construct($context);
    }

    public function getCategoryThumbUrl($category)
    {
        $url = false;
        $image = $category->getThumbnail();
        if ($image) {
            if (is_string($image)) {
                $url = $this->storeManager->getStore()->getBaseUrl(
                                \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                        ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while getting the image url.')
                );
            }
        }

        return $url;
    }

    public function getObjectManager() {
        return $this->objectManager;
    }
}
