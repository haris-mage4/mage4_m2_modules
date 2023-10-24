<?php

namespace Mage4\BookingPerTime\Helper;

use Magento\Framework\App\Helper\Context;

class BookOptions extends \Magetop\Bookingonline\Helper\BookOptions
{
    protected $customerSession;
    /**
     * @var TimeHelper
     */
    protected $timeHelper;
    /**
     * @var BookPrice
     */
    protected $bookPrice;
    /**
     * @var \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory
     */
    protected $bookItems;

    public function __construct(Context $context, \Magento\Framework\Json\Helper\Data $jsonHelper, \Magento\Backend\Model\UrlInterface $backendUrl, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Module\Manager $moduleManager, \Magento\Customer\Model\Session $customerSession, \Magetop\Bookingonline\Helper\TimeHelper $timeHelper, BookPrice $bookPrice, \Magetop\Bookingonline\Model\ResourceModel\BookingItems\CollectionFactory $bookItems)
    {
        parent::__construct($context, $jsonHelper, $backendUrl, $storeManager, $moduleManager, $customerSession, $timeHelper, $bookPrice, $bookItems);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     */
    function getBookOptions($product, $params = array(), $storeId = null)
    {


        $dividend = $params['qty'];
        $divisor = 7;
        $numberOfTransfers = intdiv($dividend, $divisor);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/custom.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('Custom message');
        $logger->info(print_r($params, true));
        $logger->info($dividend);
        $logger->info($numberOfTransfers);
        $bookOptions = array();
        $arSelect = array('book_type', 'book_product_id', 'disable_days');
        $arFilter = array();
        $productId = $product->getId();
        if (!$storeId) {
            $storeId = (int)$this->getBoCurrentStoreId();
        }
        $bookItems = $this->bookItems->create()
            ->getBookItemsByProductId($productId, $arFilter, $arSelect, $storeId);
        $bookItem = $bookItems->getFirstItem() ? $bookItems->getFirstItem() : null;
        $checkIn = isset($params['start_date']) ? $params['start_date'] : '';
        $formatDate = $this->timeHelper->getBoFormatDate();
        $okAdd = false;
        $message = __('Service is not available , Please select other dates');
        $totalPrice = 0;
        if ($this->timeHelper->validateBoDate($checkIn, $formatDate))
            $checkIn = $this->timeHelper->convertBoDate($checkIn);
        else
            $checkIn = '';
        if (!count($params) || $checkIn == '' || !$bookItem) {

        } else {
            $bookType = $bookItem->getData('book_type');
            $prices = array();
            $qty = isset($params['qty']) ? $params['qty'] : 1;
            $options = isset($params['book_options']) ? $params['book_options'] : array();
            $timeBook = isset($params['time_book']) ? $params['time_book'] : time();
            if ($bookType == 'per_day' || $bookType == 'per_night') {
                $bookOptions[] = array(
                    'label' => __('Check In'),
                    'value' => date($formatDate, strtotime($checkIn)),
                    'code' => $timeBook
                );
                $checkOut = isset($params['end_date']) ? $params['end_date'] : '';
                if ($this->timeHelper->validateBoDate($checkOut, $formatDate))
                    $checkOut = $this->timeHelper->convertBoDate($checkOut);
                else {
                    $checkOut = '';
                }
                if ($checkOut != '') {
                    $prices = $this->bookPrice->getDayPrice($params, $storeId);
                    if ($prices['status'] == 'success') {
                        $okAdd = true;
                        $bookOptions[] = array(
                            'label' => __('Check Out'),
                            'value' => date($formatDate, strtotime($prices['end_date']))
                        );
                        $bookOptions[] = array(
                            'label' => __('Total Days'),
                            'value' => $prices['total_days']
                        );
                    } else {
                        $message = $prices['message'];
                    }
                }

            } elseif ($bookType === 'per_time') {
                $prices = $this->bookPrice->getTimeSlotPrice($params, $storeId);
                if ($prices['status'] == 'success') {
                    $bookOptions[] = array(
                        'label' => __('Date'),
                        'value' => date($formatDate, strtotime($checkIn)),
                        'code' => time()
                    );
                    $okAdd = true;
                    $timeSlotLabels = $prices['time_slot_labels'];
                    if ($timeSlotLabels != '')
                        $timeSlotLabels = implode(',', $timeSlotLabels);
                    else
                        $timeSlotLabels = '';
                    $bookOptions[] = array(
                        'label' => __('Time Slots'),
                        'value' => $timeSlotLabels
                    );
                    if (isset($params['book_ticket_seat']) && trim($params['book_ticket_seat']) != '') {
                        $seats = explode(',', $params['book_ticket_seat']);
                        $txtSeats = '';
                        foreach ($seats as $seat) {
                            $seat = explode('_', $seat);
                            if ($txtSeats != '')
                                $txtSeats .= ', ' . $seat[1];
                            else
                                $txtSeats = $seat[1];
                        }
                        $bookOptions[] = array(
                            'label' => __('Seat'),
                            'value' => $txtSeats
                        );
                    }
                } else {
                    $message = $prices['message'];
                }
            } elseif ($bookType == 'per_hour') {
                $prices = $this->bookPrice->getTimeSlotPrice($params, $storeId);
                if ($prices['status'] == 'success') {
                    $bookOptions[] = array(
                        'label' => __('Date'),
                        'value' => date($formatDate, strtotime($checkIn)),
                        'code' => time()
                    );
                    $okAdd = true;
                    $serviceStart = isset($params['start_hours']) ? $params['start_hours'] : '';
                    $serviceEnd = isset($params['end_hours']) ? $params['end_hours'] : '';
                    if ($serviceStart != '' && $serviceEnd != '') {
                        $bookOptions[] = array(
                            'label' => __('Service Start'),
                            'value' => $serviceStart,
                            'code' => time()
                        );
                        $bookOptions[] = array(
                            'label' => __('Service End'),
                            'value' => $serviceEnd,
                            'code' => time()
                        );
                    }
                    if (isset($params['book_ticket_seat']) && trim($params['book_ticket_seat']) != '') {
                        $seats = explode(',', $params['book_ticket_seat']);
                        $txtSeats = '';
                        foreach ($seats as $seat) {
                            $seat = explode('_', $seat);
                            if ($txtSeats != '')
                                $txtSeats .= ', ' . $seat[1];
                            else
                                $txtSeats = $seat[1];
                        }
                        $bookOptions[] = array(
                            'label' => __('Seat'),
                            'value' => $txtSeats
                        );
                    }
                } else {
                    $message = $prices['message'];
                }
            }
            if (isset($prices['select_options']) && $prices['select_options']) {
                foreach ($prices['select_options'] as $option) {
                    $bookOptions[] = array(
                        'label' => $option['label'],
                        'value' => $option['value']
                    );
                }
            }
            if (isset($prices['status']) && $prices['status'] == 'success')
                $totalPrice = $prices['promo'] > 0 ? $prices['promo'] : $prices['price'];
        }
        //  $this->customerSession->setBkoBookPrice($totalPrice);
        if (!empty($params['privateTransferFirst'])) {
            $totalPrice = $totalPrice + ($params['privateTransferFirst'] * $numberOfTransfers);
            $this->customerSession->setBkoBookPrice($totalPrice);
        } else {
            $this->customerSession->setBkoBookPrice($totalPrice);
        }
        if ($okAdd) {
            return array(
                'status' => 'success',
                'message' => '',
                'data' => $bookOptions
            );
        }
        return array(
            'status' => 'error',
            'message' => $message,
            'data' => array()
        );
    }

    public function getFlightOptions($params)
    {
        $single = isset($params['flight_single']) ? (int)$params['flight_single'] : 0;
        $returnPrice = isset($params['flight_return']) ? (int)$params['flight_return'] : 0;
        $options = isset($params['book_options']) ? $params['book_options'] : [];
        $price = $this->bookPrice->getFlightPrice($single, $returnPrice, $options);

        $this->customerSession->setBkoBookPrice($price);
        $response = [
            'status' => 'error',
            'message' => __('No flight available on the date'),
            'data' => array()
        ];
        if ($price > 0) {
            $response['status'] = 'success';
            $response['message'] = '';
        }
        return $response;
    }
}
