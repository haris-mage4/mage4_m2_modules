<?php

namespace Mage4\BookingExtend\Helper;

use Magento\Framework\App\Helper\Context;

/**
 * Class StandardPrice
 * @package Magetop\Bookstandard\Helper
 */
class StandardPrice extends \Magetop\Bookstandard\Helper\StandardPrice
{
    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory
     */
    protected $priceHelper;
    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory
     */
    protected $bookingItemsFactory;
    /**
     * @var \Magetop\Bookingonline\Helper\TimeHelper
     */
    protected $bookTimeHelper;
    /**
     * @var \Magetop\Bookingonline\Helper\Price\DayPrice
     */
    protected $dayPrice;
    /**
     * @var \Magetop\Bookingonline\Helper\Price\DayPrice
     */
    protected $slotPrice;
    /**
     * @var
     */
    protected $priceItems;
    /**
     * @var
     */
    protected $timeSlots;
    /**
     * @var \Magetop\Bookstandard\Model\ResourceModel\Rooms\CollectionFactory
     */
    protected $roomsFactory;
    /**
     * @var RoomPrice
     */
    protected $roomPrice;
    /**
     * @var TourPrices
     */
    protected $tourPrices;

    /**
     * StandardPrice constructor.
     * @param Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory $bookingItemsFactory
     * @param \Magetop\Bookingonline\Helper\TimeHelper $bookTimeHelper
     * @param \Magetop\Bookstandard\Model\ResourceModel\Rooms\CollectionFactory $roomsFactory
     * @param RoomPrice $roomPrice
     * @param TourPrices $tourPrices
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory $bookingItemsFactory,
        \Magetop\Bookingonline\Helper\TimeHelper $bookTimeHelper,
        \Magetop\Bookstandard\Model\ResourceModel\Rooms\CollectionFactory $roomsFactory,
        \Magetop\Bookstandard\Helper\RoomPrice $roomPrice,
        \Magetop\Bookstandard\Helper\TourPrices $tourPrices
    )
    {
        parent::__construct($context, $jsonHelper, $backendUrl, $storeManager,$moduleManager,$priceHelper,$bookingItemsFactory,$bookTimeHelper,$roomsFactory,$roomPrice,$tourPrices);
        $this->priceHelper = $priceHelper;
        $this->bookingItemsFactory = $bookingItemsFactory;
        $this->bookTimeHelper = $bookTimeHelper;
        $this->roomsFactory = $roomsFactory;
        $this->roomPrice = $roomPrice;
        $this->tourPrices = $tourPrices;
    }

    /**
     * @param $params
     * @param int $storeId
     * @param bool $afterCart
     * @return array
     */
    public function getRoomPrice($params, $storeId = null, $afterCart = false) {
        $roomId = $params['room_id'];
        $formatDate = $this->bookTimeHelper->getBoFormatDate();
        $currentTime = $this->bookTimeHelper->getBoCurrentTime('Y-m-d');
        $currentTime = strtotime($currentTime);
        if($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $response = array(
            'status'=>'error',
            'message'=>__('Service Is not available , Please select other dates'),
            'price'=>0,
            'promo'=>0,
            'start_date'=>'',
            'end_date'=>''
        );
        $hotelSarSelect = array(
            'book_type',
            'min_days',
            'store_id',
            'disable_days',
            'book_version'

        );
        $bookItem = null;
        if($roomId > 0) {
            // get book item
            $room = null;
            $okTime = false;
            // check time and convert date to Y-m-d format
            $startDate = isset($params['start_date']) ? $params['start_date'] : '';
            $endDate =  isset($params['start_date']) ? $params['end_date'] : '';
            $textStartDate = $startDate;
            $textEndDate = $endDate;
            $productId =  isset($params['product']) ? $params['product'] : '';
            $qty = isset($params['qty']) ? $params['qty'] : 1;
            if($afterCart)
            {
                $qty = 0;
            }
            $options  = isset($params['book_options']) ? $params['book_options'] : array();
            if($this->bookTimeHelper->validateBoDate($startDate,$formatDate) || $this->bookTimeHelper->validateBoDate($endDate,$formatDate)) {
//                echo "in-if"."<br>";
                $startDate = $this->bookTimeHelper->convertBoDate($startDate);
                $endDate = $this->bookTimeHelper->convertBoDate($endDate);
                $collection = $this->bookingItemsFactory->create()->getBookItemsByProductId($productId,array(),$hotelSarSelect,$storeId);
                $bookItem = (count($collection) && $collection->getFirstItem()) ? $collection->getFirstItem() : null;
                if($currentTime <= strtotime($startDate) && strtotime($endDate) >= strtotime($startDate))
                {
                    $arFilter = array('room_id'=>$roomId);
                    $collection = $this->roomsFactory->create()->getAllRoomItems($arFilter);
                    $room = (count($collection) && $collection->getFirstItem()) ? $collection->getFirstItem() : null;
                    $okTime = true;
                }
            }
            // if book item and time ok

            if($okTime && $bookItem && $room)
            {
                $adults = isset($params['max_adult']) ? (int)$params['max_adult'] : 1;
                $child = isset($params['max_child']) ? (int)$params['max_child'] : 0;
                $infant = isset($params['max_infant']) ? (int)$params['max_infant'] : 0;
                $qtyPrice = $adults + $child + $infant;
                $bookItemVersion = $bookItem->getData('book_version') ? $bookItem->getData('book_version') : 1;
                $itemId = isset($params['book_sale_id']) ? $params['book_sale_id'] : 0;
                $response = $this->roomPrice->getRoomPrice($room,$startDate,$endDate,$qty,$bookItem,$options,$storeId,$itemId);
                $response['text_start_date'] = $textStartDate;
                $response['text_end_date'] = $textEndDate;
                if($this->bookTimeHelper->checkProVersion() && $bookItemVersion > 1) {
                    $response['price'] = $adults * $response['price'];
                    $response['child_price'] = $child * $response['child_price'];
                    $response['infant_price'] = $infant * $response['infant_price'];
                    $response['promo'] = $adults * $response['promo'];
                    $response['child_promo'] = $child * $response['child_promo'];
                    $response['infant_promo'] = $infant * $response['infant_promo'];
                    $response['total_price'] = $response['price']  + $response['child_price'] + $response['infant_price'];
                    $response['total_promo'] = 0;
                    if($response['promo'] > 0) {
                        $response['total_promo'] = $response['promo'] + $response['child_promo'] + $response['infant_promo'];
                    }
                }
                else {
                    //$response['price'] = $response['price'] * $qtyPrice;
                   // $response['promo'] = $response['promo'] * $qtyPrice;
                    $response['total_price'] = $response['price'];
                    $response['total_promo'] = 0;
                    if($response['promo'] > 0) {
                        $response['total_promo'] = $response['promo'];
                    }
                }
                $response['book_version'] = $bookItemVersion;
            }
        }
        return $response;
    }

    /**
     * @param $params
     * @param int $storeId
     * @param bool $afterCart
     * @return array
     */
    function getTourPrices(array $params, $storeId = null, $afterCart = false) {
        $productId = $params['product'];
//        echo $productId.'===';exit;
        $formatDate = $this->bookTimeHelper->getBoFormatDate();
        $currentTime = $this->bookTimeHelper->getBoCurrentTime('Y-m-d');
        $currentTime = strtotime($currentTime);
        if($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $response = array(
            'status'=>'error',
            'message'=>__('Service Is not available , Please select other dates'),
            'price'=>0,
            'promo'=>0,
            'child_price'=>0,
            'infant_price'=>0,
            'child_promo'=>0,
            'infant_promo'=>0,
            'start_date'=>'',
            'end_date'=>''
        );
        $arSelect = array(
            'book_product_id',
            'book_type',
            'min_days',
            'store_id',
            'disable_days',
            'tour_type',
            'tour_default_day',
            'book_version'
        );
        if($productId > 0) {
            // get book item
            $bookItem = null;
            $okTime = false;
            // check time and convert date to Y-m-d format
            $startDate = isset($params['start_date']) ? $params['start_date'] : '';
            $endDate =  isset($params['start_date']) ? $params['end_date'] : '';
            $adults = isset($params['max_adult']) ? (int)$params['max_adult'] : 0;
            $child = isset($params['max_child']) ? (int)$params['max_child'] : 0;
            $infant = isset($params['max_infant']) ? (int)$params['max_infant'] : 0;
            $qty = isset($params['qty']) ? $params['qty'] : 1;
            $textStartDate = $startDate;
            $textEndDate = $endDate;
            if($afterCart)
            {
                $qty = 0;
            }
            $options  = isset($params['book_options']) ? $params['book_options'] : array();
            if($this->bookTimeHelper->validateBoDate($startDate,$formatDate) && $this->bookTimeHelper->validateBoDate($endDate,$formatDate)) {
                $startDate = $this->bookTimeHelper->convertBoDate($startDate);
                $endDate = $this->bookTimeHelper->convertBoDate($endDate);
                $startDate = $endDate;
                if($currentTime <= strtotime($startDate) && strtotime($endDate) >= strtotime($startDate))
                {
                    $collection = $this->bookingItemsFactory->create()->getBookItemsByProductId($productId,array(),$arSelect,$storeId);
                    $bookItem = (count($collection) && $collection->getFirstItem()) ? $collection->getFirstItem() : null;
                    $okTime = true;
                }
            }
            // if book item and time ok
            if($okTime && $bookItem)
            {
                $qty = $adults + $child + $infant;
                $itemId = isset($params['book_sale_id']) ? $params['book_sale_id'] : 0;
                $response = $this->tourPrices->getTourPrice($bookItem,$startDate,$endDate,$adults,$child,$infant,$qty,$options,$storeId,$itemId);
                $bookVersion = $response['book_version'];
                $response['text_start_date'] = $textStartDate;
                $response['text_end_date'] = $textEndDate;
                if($this->checkProVersion() && $bookVersion > 1) {
                    $response['child_price'] *= $child;
                    $response['infant_price'] *= $infant;
                    $response['child_promo'] = $child * $response['child_promo'];
                    $response['infant_promo'] = $infant * $response['infant_promo'];
                    $response['price'] *= $adults;
                    $response['promo'] *= $adults;
                    $response['total_price'] = $response['price']  + $response['child_price'] + $response['infant_price'];
                    $response['total_promo'] = 0;
                    if($response['promo'] > 0) {
                        $response['total_promo'] = $response['promo'] + $response['child_promo'] + $response['infant_promo'];
                    }
                }
                else {
                    $response['price'] *= $qty;
                    $response['promo'] *= $qty;
                    $response['total_price'] = $response['price'];
                    $response['total_promo'] = 0;
                    if($response['promo'] > 0) {
                        $response['total_promo'] = $response['promo'];
                    }
                }

                $response['max_adult'] = $params['max_adult'];
                $response['max_child'] = $params['max_child'];
                $response['privateTransferFirst'] = $params['privateTransferFirst']??null;
            }
        }
        return $response;
    }
    public function getBookTimeHelper() {
        return $this->bookTimeHelper;
    }
}
