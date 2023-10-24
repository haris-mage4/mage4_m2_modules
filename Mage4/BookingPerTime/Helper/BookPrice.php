<?php

namespace Mage4\BookingPerTime\Helper;

use Magento\Framework\App\Helper\Context;

class BookPrice extends \Magetop\Bookingonline\Helper\BookPrice
{

    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory
     */
    protected $priceHelper;
    protected $cart;
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
     * @var \Magetop\Bookingonline\Model\ResourceModel\PriceItems\CollectionFactory
     */
    protected $priceItems;
    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\TimeSlots\CollectionFactory
     */
    protected $timeSlots;
    protected $bookOrdersFactory;
    protected $optionPrice;
  public function __construct(Context $context, \Magento\Framework\Json\Helper\Data $jsonHelper,\Magetop\Bookingonline\Helper\TimeHelper $bookTimeHelper, \Magento\Backend\Model\UrlInterface $backendUrl, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Module\Manager $moduleManager, \Magento\Framework\Pricing\Helper\Data $priceHelper, \Magento\Checkout\Model\Cart $cart, \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory $bookingItemsFactory, \Magetop\Bookingonline\Helper\Price\DayPrice $dayPrice, \Magetop\Bookingonline\Helper\Price\SlotPrice $slotPrice, \Magetop\Bookingonline\Model\ResourceModel\PriceItems\CollectionFactory $priceItems, \Magetop\Bookingonline\Model\ResourceModel\TimeSlots\CollectionFactory $timeSlots, \Magetop\Bookingonline\Model\ResourceModel\BookOrders\CollectionFactory $bookOrdersFactory, \Magetop\Bookingonline\Helper\Price\OptionPrice $optionPrice)
  {
      parent::__construct($context, $jsonHelper, $backendUrl, $storeManager, $moduleManager, $priceHelper, $cart, $bookingItemsFactory, $bookTimeHelper, $dayPrice, $slotPrice, $priceItems, $timeSlots, $bookOrdersFactory, $optionPrice);
      $this->bookTimeHelper = $bookTimeHelper;
      $this->dayPrice = $dayPrice;
      $this->slotPrice = $slotPrice;
      $this->optionPrice = $optionPrice;
  }

    /**
     * @param array $params
     * @param null $storeId
     * @param bool $afterCart
     * @param array $moreOptions
     * @return array
     */
    public function getDayPrice(array $params, $storeId = null, $afterCart = false,$isGroup = 0, $itemId = 0, $aferOder = 0) {
        $productId = $params['product'];
        $formatDate = $this->bookTimeHelper->getBoFormatDate();
        $currentTime = $this->bookTimeHelper->getBoCurrentTime('Y-m-d');
        $currentTime = strtotime($currentTime);
        if(!$storeId) {
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
        $arSelect = array(
            'book_product_id',
            'book_type',
            'min_days',
            'store_id',
            'disable_days',
            'default_days'
        );
        if($productId > 0) {
            // get book item
            $bookItem = null;
            $okTime = false;
            // check time and convert date to Y-m-d format
            $startDate = isset($params['start_date']) ? $params['start_date'] : '';
            $endDate =  isset($params['start_date']) ? $params['end_date'] : '';
            $textStartDate = $startDate;
            $textEndDate = $endDate;
            $qty = isset($params['qty']) ? $params['qty'] : 1;
            if($afterCart)
            {
                $qty = 0;
            }
            $options  = isset($params['book_options']) ? $params['book_options'] : array();
            if($this->bookTimeHelper->validateBoDate($startDate,$formatDate) && $this->bookTimeHelper->validateBoDate($endDate,$formatDate)) {
                $startDate = $this->bookTimeHelper->convertBoDate($startDate);
                $endDate = $this->bookTimeHelper->convertBoDate($endDate);
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
                // get price data
                if($bookItem->getData('book_type') == 'per_day' || $bookItem->getData('book_type') == 'per_night')
                {
                    $moreOptions['is_group'] = $isGroup;
                    $moreOptions['after_order'] = $aferOder;
                    $response = $this->dayPrice->getPerDayPrice($bookItem,$startDate,$endDate,$qty,$options,$storeId,$itemId, $moreOptions);
                    $response['text_start_date'] = $textStartDate;
                    $response['text_end_date'] = $textEndDate;

                }
            }
        }
        $response['qty'] = $params['qty'];
        $response['privateTransferFirst'] = $params['privateTransferFirst']??null;
        return $response;
    }

    /**
     * @param $params
     * @param int $storeId
     * @param bool $afterCart
     * @return array
     */
    function getTimeSlotPrice($params, $storeId = null, $afterCart = false,$itemId = 0) {
        $productId = isset($params['product']) ? $params['product'] : 0;
        $formatDate = $this->bookTimeHelper->getBoFormatDate();
        $currentTime = $this->bookTimeHelper->getBoCurrentTime('Y-m-d');
        $bookType = isset($params['book_type']) ? $params['book_type'] : 'per_time';
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
        $arSelect = array(
            'book_product_id',
            'book_type',
            'min_days',
            'store_id',
            'disable_days',
            'show_end_time',
            'show_qty',
            'min_hours'
        );
        $startDate = isset($params['start_date']) ? $params['start_date'] : '';
        if($productId > 0) {
            // get book item
            $bookItem = null;
            $okTime = false;
            // check time and convert date to Y-m-d format

            $qty = isset($params['qty']) ? $params['qty'] : 1;
            if($afterCart)
            {
                $qty = 0;
            }
            $options  = isset($params['book_options']) ? $params['book_options'] : array();
            $timeSlots  = isset($params['time_slots']) ? $params['time_slots'] : array();
            if($this->bookTimeHelper->validateBoDate($startDate,$formatDate)) {
                $startDate = $this->bookTimeHelper->convertBoDate($startDate);
                if($currentTime <= strtotime($startDate))
                {
                    $collection = $this->bookingItemsFactory->create()->getBookItemsByProductId($productId,array(),$arSelect,$storeId);
                    $bookItem = (count($collection) && $collection->getFirstItem()) ? $collection->getFirstItem() : null;
                    $okTime = true;
                }
            }
            // if book item and time ok
            if($okTime && $bookItem)
            {
                // get price data
                if($bookType == 'per_hour')
                {
                    $startHours = isset($params['start_hours']) ? $params['start_hours'] : '';
                    $endHours = isset($params['end_hours']) ? $params['end_hours'] : '';
                    $startHours = strtotime($startHours);
                    $endHours = strtotime($endHours);
                    if($endHours <= $startHours)
                    {
                        $response['message'] = __('End time must be late start time');
                    }
                    else
                    {
                        $minHours = $bookItem->getData('min_hours');
                        $priceItem = $this->priceItems->create()->getPriceItemByDate($productId,$startDate,$bookType);
                        $priceId = 0;
                        if($priceItem && $priceItem->getFirstItem())
                        {
                            $priceItem = $priceItem->getFirstItem();
                            $priceId = $priceItem->getId();
                        }
                        $arFilter = array('status'=>1);
                        $arSelect = array('time_start','time_end','price','slot_qty');
                        $timeSlotItems = $this->timeSlots->create()->getAllTimeSlotItemsById($priceId,$arFilter,$arSelect);
                        $timeSlots = array();
                        foreach ($timeSlotItems as $timeSlotItem)
                        {
                            if(strtotime($timeSlotItem->getData('time_start')) >= $startHours && strtotime($timeSlotItem->getData('time_end')) <= $endHours)
                            {
                                $timeSlots[] = $timeSlotItem->getId();

                            }
                        }
                        if($minHours > count($timeSlots))
                            $response['message'] = __('Minimum hour(s) is %1, please check again ',$minHours);
                        else
                            $response = $this->slotPrice->getSlotPrice($bookItem,$startDate,$qty,$timeSlots,$options,$storeId,$itemId);
                    }
                }
                else {
                    $response = $this->slotPrice->getSlotPrice($bookItem,$startDate,$qty,$timeSlots,$options,$storeId,$itemId);

                }

            }
        }
        $itemId = isset($params['book_sale_id']) ? $params['book_sale_id'] : 0;
        $slots = isset($params['time_slots']) ? $params['time_slots'] : array();
        $slotId = isset($slots[0]) ? $slots[0] : 0;
        if($bookType == 'per_hour')
            $slotId = isset($params['tmp_slot_slot_id']) ? $params['tmp_slot_slot_id'] : 0;
        $cartSeat = $this->getSeatInCarts($productId,$startDate,$slotId,$bookType);
        $orderSeat = $this->getBookSeatOrders($productId,$startDate,$slotId);
        $response['cart_seat'] = $cartSeat;
        if($response['cart_seat'] == '')
            $response['cart_seat'] = $orderSeat;
        else
            $response['cart_seat'] .= ','.$orderSeat;
        $response['cur_seat'] = $this->getCurSeatInCart($itemId,$slotId,$startDate,$bookType);
        $response['qty'] = $params['qty'];
        $response['privateTransferFirst'] = $params['privateTransferFirst']??null;
        return $response;
    }
    function getSeatInCarts($productId,$startDate,$slotId,$bookType = 'per_time') {
        $items = $this->cart->getQuote()->getAllVisibleItems();
        $strSeat  = '';
        if(count($items)) {
            foreach ($items as $item) {
                $customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptionsRequest = $customOptions['info_buyRequest'];
                if(!isset($customOptionsRequest['start_date']) || trim($customOptionsRequest['start_date']) == '')
                    continue;

                $checkIn = $this->bookTimeHelper->convertBoDate($customOptionsRequest['start_date']);
                $tmpSeat =  isset($customOptionsRequest['book_ticket_seat']) ? $customOptionsRequest['book_ticket_seat'] : '';
                if($productId != $item->getProduct()->getId() || $checkIn != $startDate || $tmpSeat == '')
                    continue;
                if($bookType == 'per_time') {
                    $itemSlots = isset($customOptionsRequest['time_slots']) ? $customOptionsRequest['time_slots'] : array();
                    if(in_array($slotId,$itemSlots)) {
                        if($strSeat == '')
                            $strSeat = $tmpSeat;
                        else
                            $strSeat .= ','.$tmpSeat;
                    }
                }
                else {
                    $txtSlotId = isset($customOptionsRequest['tmp_slot_slot_id']) ? $customOptionsRequest['tmp_slot_slot_id'] : '';
                    if($slotId == $txtSlotId) {
                        if($strSeat == '')
                            $strSeat = $tmpSeat;
                        else
                            $strSeat .= ','.$tmpSeat;
                    }
                }
            }
        }
        return $strSeat;
    }
    function getCurSeatInCart($itemId,$slotId,$startDate,$bookType) {
        $strSeat  = '';
        $items = $this->cart->getQuote()->getAllVisibleItems();
        if(count($items)) {
            foreach ($items as $item) {
                $customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
                $customOptionsRequest = $customOptions['info_buyRequest'];
                if(!isset($customOptionsRequest['start_date']) || trim($customOptionsRequest['start_date']) == '')
                    continue;
                $checkIn = $this->bookTimeHelper->convertBoDate($customOptionsRequest['start_date']);
                $tmpSeat =  isset($customOptionsRequest['book_ticket_seat']) ? $customOptionsRequest['book_ticket_seat'] : '';
                if($checkIn != $startDate || $tmpSeat == '')
                    continue;
                if($item->getItemId() == $itemId) {
                    if($bookType == 'per_time') {
                        $itemSlots = isset($customOptionsRequest['time_slots']) ? $customOptionsRequest['time_slots'] : array();
                        if(in_array($slotId,$itemSlots)) {
                            if($strSeat == '')
                                $strSeat = $tmpSeat;
                            else
                                $strSeat .= ','.$tmpSeat;
                        }
                    }
                    else {
                        $txtSlotId = isset($customOptionsRequest['tmp_slot_slot_id']) ? $customOptionsRequest['tmp_slot_slot_id'] : '';
                        if($slotId == $txtSlotId) {
                            if($strSeat == '')
                                $strSeat = $tmpSeat;
                            else
                                $strSeat .= ','.$tmpSeat;
                        }
                    }
                }
            }
        }
        return $strSeat;
    }
    function getBookSeatOrders($productId,$startDate,$slotId) {
        $arFilter = array('check_in'=>$startDate,'book_product_id'=>$productId);
        $collection = $this->bookOrdersFactory->create()->getAllOrderItems($arFilter,array('ticket_seats'));
        $collection->addFieldToFilter('interval_ids',array('finset'=>$slotId));
        $strSeat  = '';
        if($collection && $collection->getSize() > 0) {
            foreach ($collection as $item) {
                $seat = trim($item->getData('ticket_seats'));
                if($seat == '')
                    continue;
                if($strSeat == '')
                    $strSeat = $seat;
                else
                    $strSeat .= ','.$seat;

            }
        }
        return $strSeat;
    }
    /**
     * @param int $singleId
     * @param int $returnId
     * @param array $options
     * @return array|int|mixed|null
     */
    public function getFlightPrice($singleId = 0, $returnId = 0, $options = []) {
        $price = 0;
        $addonPrice = 0;
        if(count($options)) {
            $storeId = $this->getBoCurrentStoreId();
            $optionPrice = $this->optionPrice->getOptionPrices(33,'per_time',$options, $storeId);

            if(isset($optionPrice['status']) && $optionPrice['status'] == 'success') {
                $addonPrice = $optionPrice['price'];
            }

        }
        if($singleId > 0) {
            $collection = $this->timeSlots->create()
                ->addFieldToFilter('slot_id',$singleId);
            if($collection && $collection->getSize() > 0) {
                $price += $collection->getFirstItem()->getData('price') + $addonPrice;
            }
        }
        if($returnId > 0) {
            $collection = $this->timeSlots->create()
                ->addFieldToFilter('slot_id',$returnId);
            if($collection && $collection->getSize() > 0) {
                $price += $collection->getFirstItem()->getData('price') + $addonPrice;
            }
        }



        return $price;
    }
}
