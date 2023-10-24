<?php
/******************************************************************************
 *     ______                                                                 *
 *    /       \                                                               *
 *   /$$$$$$  | ________  __    __   ______    ______
 *   $$ |__$$ |/        |/  |  /  | /      \  /      \                        *
 *   $$    $$ |$$$$$$$$/ $$ |  $$ |/$$$$$$  |/$$$$$$  |                       *
 *   $$$$$$$$ |  /  $$/  $$ |  $$ |$$ |  $$/ $$    $$ |                       *
 *   $$ |  $$ | /$$$$/__ $$ \__$$ |$$ |      $$$$$$$$/                        *
 *   $$ |  $$ |/$$      |$$    $$/ $$ |      $$       |                       *
 *   $$/   $$/ $$$$$$$$/  $$$$$$/  $$/        $$$$$$$/                        *
 *                                                                            *
 *   @PROJECT    : Book Extension [Bookingonlinepro.com]                      *
 *   @AUTHOR     : Azure - Developer                                          *
 *   @COPYRIGHT  : © 2019  Bookingonlinepro.com                               *
 *   @LINK       : https://bookingonlinepro.com                               *
 *   @CREATED    :  10/04/2019                                                *
 ******************************************************************************/

namespace Mage4\BookingExtend\Helper;


use Magento\Framework\App\Helper\Context;

/**
 * Class BookOptions
 * @package Magetop\Bookingonline\Helper
 */
class BookOptions extends \Magetop\Bookstandard\Helper\BookOptions
{
    protected $priceHelper;
    protected $customerSession;

    /**
     * @var TimeHelper
     */
    protected $timeHelper;
    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory
     */
    protected $bookItems;
    /**
     * @var StandardPrice
     */
    protected $standardPrice;
    /**
     * @var \Magetop\Bookstandard\Model\ResourceModel\Rooms\CollectionFactory
     */
    protected $roomItems;
    /**
     * @var \Magetop\Bookstandard\Model\ResourceModel\RoomTypes\CollectionFactory
     */
    protected $roomTypes;

    public function __construct(
       Context $context,
       \Magento\Framework\Json\Helper\Data $jsonHelper,
       \Magento\Backend\Model\UrlInterface $backendUrl,
       \Magento\Store\Model\StoreManagerInterface $storeManager,
       \Magento\Framework\Module\Manager $moduleManager,
       \Magento\Framework\Pricing\Helper\Data $priceHelper,
       \Magento\Customer\Model\Session $customerSession,
       \Magetop\Bookingonline\Helper\TimeHelper $timeHelper,
       \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory $bookItems,
       \Magetop\Bookstandard\Helper\StandardPrice $standardPrice,
       \Magetop\Bookstandard\Model\ResourceModel\Rooms\CollectionFactory $roomItems,
       \Magetop\Bookstandard\Model\ResourceModel\RoomTypes\CollectionFactory $roomTypes
   )
   {
       parent::__construct($context, $jsonHelper, $backendUrl, $storeManager,$moduleManager,$priceHelper,$customerSession,$timeHelper,$bookItems,$standardPrice,$roomItems,$roomTypes);
       $this->priceHelper = $priceHelper;
       $this->customerSession = $customerSession;
       $this->timeHelper = $timeHelper;
       $this->bookItems = $bookItems;
       $this->standardPrice = $standardPrice;
       $this->roomItems = $roomItems;
       $this->roomTypes = $roomTypes;
   }


    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @param null $storeId
     * @return array
     */
    function getBookOptions(
        \Magento\Catalog\Model\Product $product,
        $params = array(),
        $storeId = null
    ) {
        $bookOptions = array();
        $arSelect = array('room_type');
        $roomId = isset($params['room_id']) ? $params['room_id'] : 0;
        $arFilter = array('room_id'=>$roomId,'status'=>1);
        $bookType = isset($params['book_type']) ? trim($params['book_type']) : '';
        $productId = $product->getId();
        if(!$storeId) {
            $storeId = (int)$this->getBoCurrentStoreId();
        }
        $bookItems = $this->bookItems->create()
            ->getBookItemsByProductId($productId,array(),array('book_type'),$storeId);
        $bookItem = $bookItems->getFirstItem() ? $bookItems->getFirstItem() : null;
        $roomItems = $this->roomItems->create()
           ->getAllRoomItems($arFilter,$arSelect);
        $roomItem = $roomItems->getFirstItem() ? $roomItems->getFirstItem() : null;
        $checkIn = isset($params['start_date']) ? $params['start_date'] : '';
        $formatDate = $this->timeHelper->getBoFormatDate();
        $okAdd = false;
        $message = __('Service is not available , Please select other dates');
        $adults = isset($params['max_adult']) ? (int)$params['max_adult'] : 1;
        $child = isset($params['max_child']) ? (int)$params['max_child'] : 0;
        $infant = isset($params['max_infant']) ? (int)$params['max_infant'] : 0;
        $totalPrice = 0;
        if($this->timeHelper->validateBoDate($checkIn,$formatDate))
            $checkIn = $this->timeHelper->convertBoDate($checkIn);
        else
            $checkIn = '';
        if(!$bookItem || !$roomItem || !count($params) || $bookType == '' || $checkIn == '')
        {

        }
        else {
            $prices = array();
            $qty = isset($params['qty']) ? $params['qty'] : 1;
            $options = isset($params['book_options']) ? $params['book_options'] : array();
            $roomTitle = $this->getRoomTypeTitle($roomItem->getData('room_type'));
            $timeBook = isset($params['time_book']) ? $params['time_book'] : time();
            $bookOptions[] = array(
                'label'=>__('Room Type'),
                'value'=>$roomTitle,
                'code'=>$timeBook
            );
            $bookOptions[] = array(
                'label'=>__('Check In'),
                'value'=>date($formatDate,strtotime($checkIn)),
                'code'=>time()
            );
            $checkOut = isset($params['end_date']) ? $params['end_date'] : '';
            if($this->timeHelper->validateBoDate($checkOut,$formatDate))
                $checkOut = $this->timeHelper->convertBoDate($checkOut);
            else
            {
                $checkOut = '';
            }
            if($checkOut != '' )
            {
                if($bookType == 'hotel') {
                    $prices = $this->standardPrice->getRoomPrice($params,$storeId);
                    if($prices['status'] == 'success')
                    {
                        $okAdd = true;
                        $bookOptions[] = array(
                            'label'=>__('Check Out'),
                            'value'=>date($formatDate,strtotime($prices['end_date']))
                        );

                        if($this->timeHelper->checkProVersion() && $prices['book_version'] > 1) {
                            $adultPrice = (isset($prices['promo']) && $prices['promo'] > 0) ? $prices['promo'] : $prices['price'];
                            if($adults > 0) {
                                $adultPrice =  $adultPrice / $adults;
                                $bookOptions[] = array(
                                    'label'=>__('Adult Price '),
                                    'value'=>$adults . ' x ' .$this->convertPriceToMoney($adultPrice)
                                );
                            }
                            if($child > 0 && isset($prices['child_price']) && (float)$prices['child_price'] > 0) {
                                $childPrice = (isset($prices['child_promo']) && $prices['child_promo'] > 0) ? $prices['child_promo'] : $prices['child_price'];
                                if($child > 0) {
                                    $childPrice = $childPrice / $child;
                                }
                                $bookOptions[] = array(
                                    'label'=>__('Child Price '),
                                    'value'=>$child . ' x ' .$this->convertPriceToMoney($childPrice)
                                );

                            }
                            if($infant > 0 && isset($prices['infant_price']) && (float)$prices['infant_price'] > 0) {
                                $infantPrice = (isset($prices['infant_promo']) && $prices['infant_promo'] > 0) ? $prices['infant_promo'] : $prices['infant_price'];
                                if($infant > 0) {
                                    $infantPrice = $infantPrice / $infant;
                                }
                                $bookOptions[] = array(
                                    'label'=>__('Infant Price: '),
                                    'value'=>$infant . ' x ' .$this->convertPriceToMoney($infantPrice)
                                );
                            }
                        }
                        else {
                            if($adults > 0) {

                                $bookOptions[] = array(
                                    'label'=>__('Adult'),
                                    'value'=>$adults
                                );
                            }
                            if($child > 0) {
                                $bookOptions[] = array(
                                    'label'=>__('Child '),
                                    'value'=>$child
                                );

                            }
                            if($infant > 0 && isset($prices['infant_price']) && (float)$prices['infant_price'] > 0) {
                                $bookOptions[] = array(
                                    'label'=>__('Infant'),
                                    'value'=>$infant
                                );
                            }
                        }
                        $bookOptions[] = array(
                            'label'=>__('Total Days'),
                            'value'=>$prices['total_days']
                        );
                        $totalPrice = $prices['total_promo'] > 0 ? $prices['total_promo'] : $prices['total_price'];
                    }
                    else
                    {
                        $message = $prices['message'];
                    }

                }

            }
            if(isset($prices['select_options']) && $prices['select_options']) {
                foreach ($prices['select_options'] as $option)
                {
                    $bookOptions[] = array(
                        'label'=>$option['label'],
                        'value'=>$option['value']
                    );
                }
            }
        }
        $this->customerSession->setBkoBookPrice($totalPrice);
        if($okAdd) {
            return  array(
                'status'=>'success',
                'message'=>'',
                'data'=>$bookOptions
            );
        }
        return  array(
            'status'=>'error',
            'message'=>$message,
            'data'=>array()
        );
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @param null $storeId
     * @return array
     */
    function getBookTourOptions(
        \Magento\Catalog\Model\Product $product,
        array $params,
        $storeId = null
    ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/customTierPrice2233.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Custom message');
        $logger->info($params);

        $dividend = $params['max_adult']+$params['max_child'];
        $divisor = 7;
        $numberOfTransfers = intdiv($dividend, $divisor);

        $logger->info($dividend);
        $logger->info($numberOfTransfers);

        $bookOptions = array();
        $arSelect = array('book_type','book_product_id','disable_days');
        $arFilter = array();
        $productId = $product->getId();
        if(!$storeId) {
            $storeId = (int)$this->getBoCurrentStoreId();
        }
        $bookItems = $this->bookItems->create()
            ->getBookItemsByProductId($productId,$arFilter,$arSelect,$storeId);
        $bookItem = $bookItems->getFirstItem() ? $bookItems->getFirstItem() : null;
        $checkIn = isset($params['start_date']) ? $params['start_date'] : '';
        $formatDate = $this->timeHelper->getBoFormatDate();
        $okAdd = false;
        $message = __('Service is not available , Please select other dates');
        $adults = isset($params['max_adult']) ? (int)$params['max_adult'] : 0;
        $child = isset($params['max_child']) ? (int)$params['max_child'] : 0;
        $infant = isset($params['max_infant']) ? (int)$params['max_infant'] : 0;
        $totalPrice = 0;
        if($this->timeHelper->validateBoDate($checkIn,$formatDate))
            $checkIn = $this->timeHelper->convertBoDate($checkIn);
        else
            $checkIn = '';
        if(!count($params) || $checkIn == '' || !$bookItem)
        {

        }
        else {
            $checkOut = isset($params['end_date']) ? $params['end_date'] : '';
            if($this->timeHelper->validateBoDate($checkOut,$formatDate))
                $checkOut = $this->timeHelper->convertBoDate($checkOut);
            else
            {
                $checkOut = '';
            }
            if($checkOut != '' )
            {
                $prices = $this->standardPrice->getTourPrices($params,$storeId);
                if($prices['status'] == 'success')
                {
                    $okAdd = true;
                    $bookOptions[] = array(
                        'label'=>__('Start Date'),
                        'value'=>date($formatDate,strtotime($prices['start_date'])),
                        'code'=>time(),
                    );
                    $bookOptions[] = array(
                        'label'=>__('End Date'),
                        'value'=>date($formatDate,strtotime($prices['end_date']))
                    );
                    if($this->timeHelper->checkProVersion() && $prices['book_version'] >= 2) {
                        $adultPrice = (isset($prices['promo']) && $prices['promo'] > 0) ? $prices['promo'] : $prices['price'];
                        if($adults > 0) {
                            $adultPrice =  $adultPrice / $adults;
                            $bookOptions[] = array(
                                'label'=>__('Adult Price '),
                                'value'=>$adults . ' x ' .$this->convertPriceToMoney($adultPrice)
                            );
                        }
                        if($child > 0 && isset($prices['child_price']) && (float)$prices['child_price'] > 0) {
                            $childPrice = (isset($prices['child_promo']) && $prices['child_promo'] > 0) ? $prices['child_promo'] : $prices['child_price'];
                            if($child > 0) {
                                $childPrice = $childPrice / $child;
                            }
                            $bookOptions[] = array(
                                'label'=>__('Child Price '),
                                'value'=>$child . ' x ' .$this->convertPriceToMoney($childPrice)
                            );

                        }
                        if($infant > 0 && isset($prices['infant_price']) && (float)$prices['infant_price'] > 0) {
                            $infantPrice = (isset($prices['infant_promo']) && $prices['infant_promo'] > 0) ? $prices['infant_promo'] : $prices['infant_price'];
                            if($infant > 0) {
                                $infantPrice = $infantPrice / $infant;
                            }
                            $bookOptions[] = array(
                                'label'=>__('Infant Price '),
                                'value'=>$infant . ' x ' .$this->convertPriceToMoney($infantPrice)
                            );
                        }
                    }
                    else {
                        if($adults > 0) {
                            $bookOptions[] = array(
                                'label'=>__('Adult '),
                                'value'=>$adults
                            );
                        }
                        if($child > 0) {
                            $bookOptions[] = array(
                                'label'=>__('Child '),
                                'value'=>$child
                            );

                        }
                        if($infant > 0) {

                            $bookOptions[] = array(
                                'label'=>__('Infant '),
                                'value'=>$infant
                            );
                        }
                    }
                    $bookOptions[] = array(
                        'label'=>__('Total Days'),
                        'value'=>$prices['total_days']
                    );
                    $totalPrice = $prices['total_promo'] > 0 ? $prices['total_promo'] : $prices['total_price'];
                }
                else
                {
                    $message = $prices['message'];
                }
            }
        }

        if (!empty($params['privateTransferFirst'])) {
            $totalPrice = $totalPrice+($params['privateTransferFirst']*$numberOfTransfers);
            $this->customerSession->setBkoBookPrice($totalPrice);
        } else {
            $this->customerSession->setBkoBookPrice($totalPrice);
        }

        if($okAdd) {
            return  array(
                'status'=>'success',
                'message'=>'',
                'data'=>$bookOptions
            );
        }
        return  array(
            'status'=>'error',
            'message'=>$message,
            'data'=>array()
        );
    }
    /**
     * @param string $roomType
     * @param int $storeId
     * @return mixed|string
     */
    private function getRoomTypeTitle($roomType = '', $storeId = 0) {
        $collection = $this->roomTypes->create()
            ->addFieldToSelect('title')
            ->addFieldToFilter('room_type',$roomType)
            ->addFieldToFilter('store_id',$storeId);
        return $collection->getFirstItem() ? $collection->getFirstItem()->getData('title') : '';
    }

    /**
     * @param $price
     * @return float|string
     */
    private function convertPriceToMoney  ($price)  {
        return $this->priceHelper->currency($price,true,false);
    }

}
