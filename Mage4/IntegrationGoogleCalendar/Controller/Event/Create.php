<?php

namespace Mage4\IntegrationGoogleCalendar\Controller\Event;

use Magento\Framework\App\Action\Action;
use Mage4\IntegrationGoogleCalendar\Helper\AccessClient;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
class Create extends Action {

    protected $accessClient;
    protected $scopeConfig;
    protected $storeScope;

    public function __construct(Context $context, AccessClient $accessClient, ScopeConfigInterface $scopeConfig)
    {
        $this->accessClient = $accessClient;
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = ScopeInterface::SCOPE_STORE;
        parent::__construct($context);
    }

    public function execute()
    {

        $this->accessClient->createAppointment(array(
            'summary' => 'Syner 2 2021',
            'location' => '800 Howard St., San Francisco, CA 94103',
            'description' => 'A chance to hear more about Google\'s developer products.',
            'start' => array(
                'dateTime' => '2021-03-07T17:06:02.000Z',
                'timeZone' => 'America/Los_Angeles',
            ),
            'end' => array(
                'dateTime' => '2021-03-07T17:06:02.000Z',
                'timeZone' => 'America/Los_Angeles',
            ),
            'recurrence' => array(
                'RRULE:FREQ=DAILY;COUNT=2'
            ),
            'attendees' => array(
                array('email' => 'servertest64@gmail.com'),
                array('email' => 'tariqsyed@gmail.com'),
            ),
            'reminders' => array(
                'useDefault' => FALSE,
                'overrides' => array(
                    array('method' => 'email', 'minutes' => 24 * 60),
                    array('method' => 'popup', 'minutes' => 10),
                ),
            ),
        ));
    }
}
