<?php

namespace Mage4\IntegrationGoogleCalendar\Observer;

use Google_Service_Calendar_EventDateTime;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\OrderItemRepositoryInterface;
use Webkul\BookingSystem\Helper\Data;
use Webkul\BookingSystem\Model\BookedFactory;
use Mage4\IntegrationGoogleCalendar\Helper\AccessClient;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $_bookingHelper;
    protected $orderRepository;

    /**
     * @var BookedFactory
     */
    protected $_booked;
    protected $accessClient;
    protected $orderItemRepository;

    /**
     * @param Data         $bookingHelper
     * @param BookedFactory $booked
     */
    public function __construct(
        Data $bookingHelper,
        BookedFactory $booked,
        AccessClient $accessClient,
        ScopeConfigInterface $scopeConfig,
        OrderRepositoryInterface $orderRepository,
        OrderItemRepositoryInterface $orderItemRepository

    ) {
        $this->_bookingHelper = $bookingHelper;
        $this->_booked = $booked;
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = ScopeInterface::SCOPE_STORE;
        $this->accessClient = $accessClient;
        $this->orderRepository = $orderRepository;
        $this->orderItemRepository = $orderItemRepository;
    }

    public function execute(Observer $observer)
    {
        $this->_bookingHelper
            ->logDataInLogger("EventCalled ");
        try {
            $orderIds = $observer->getEvent()->getData('order_ids');
            $orderId = $orderIds[0];
            $orderedItems = $this->orderRepository->get($orderId)->getItems();
            foreach ($orderedItems as $item) {
                $this->setAppointment( $item);
            }
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Observer_AfterPlaceOrder execute : ".$e->getMessage());
        }
    }

    private function setAppointment( $item){
        $helper = $this->_bookingHelper;
        $quoteItemId = $item->getQuoteItemId();
        $bookingData = $helper->getDetailsByQuoteItemId($quoteItemId);
        $productId = $item->getProductId();
        try {

            $slotId = $bookingData['slot_id'];
            $parentId = $bookingData['parent_slot_id'];
            $slotData = $helper->getSlotData($slotId, $parentId, $productId);
            $booking_from    =      $slotData['booking_from'];
            $booking_to      =      $slotData['booking_to'];
            $this->_bookingHelper->logDataInLogger("Observer_AfterPlaceOrder orderss 333: ". $booking_from);
            $this->_bookingHelper->logDataInLogger("Observer_AfterPlaceOrder orderss 444: ". $booking_to);
            $this->accessClient->createAppointment($booking_from, $booking_to);

        } catch (\Exception $e) {
            $this->_bookingHelper
                ->logDataInLogger("Observer_AfterPlaceOrder setAppointment : ".$e->getMessage());
        }
    }
}

