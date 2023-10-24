<?php

namespace Mage4\IntegrationGoogleCalendar\Helper;

use Google\Service\Exception;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Exception;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Webkul\BookingSystem\Helper\Data;
use Magento\Customer\Model\Session;



class AccessClient extends AbstractHelper {

    const APPLICATION_NAME = 'mage4_google_api/password_config/app_name';
    const AUTHENTICATION_URI = 'mage4_google_api/password_config/auth_uri';
    const TOKEN_URI = 'mage4_google_api/password_config/token_uri';
    const ACCESS_PROVIDER = 'mage4_google_api/password_config/auth_provider_cert_url';
    const REDIRECT_URI = 'mage4_google_api/password_config/redirect_uris';
    const CALENDAR_ID = 'primary';
    const CREDENTIAL_FILE = BP.'/credentials.json';
    const TOKEN_FILE = BP.'/token.json';

    protected $scopeConfig;
    protected $storeScope;
    protected $storeManager;
    protected $_bookingHelper;
    protected $customerSession;



    public function __construct(Context $context, ScopeConfigInterface $scopeConfig,  \Magento\Store\Model\StoreManagerInterface $storeManager, Data $_bookingHelper, Session $customerSession
  )    {
        $this->scopeConfig = $scopeConfig;
        $this->storeScope = ScopeInterface::SCOPE_STORE;
        $this->storeManager = $storeManager;
        $this->_bookingHelper = $_bookingHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    public function createAppointment($booking_from, $booking_to)
    {
        $timeZone =  $this->scopeConfig->getValue('general/locale/timezone', $this->storeScope);
        $street_address =  $this->scopeConfig->getValue('general/store_information/street_line1', $this->storeScope);
        $event_summary = $this->scopeConfig->getValue('mage4_google_api/password_config/event_summary', $this->storeScope);
        $service = new Google_Service_Calendar($this->getClient());
        $start = explode(",",$booking_from);
        $end = explode(",",$booking_to);
        $startDate = date("Y-m-d", strtotime($start[0]));
        $endDate = date("Y-m-d", strtotime($end[0]));
        $start_time =  date("H:i:s", strtotime( strtoupper($start[1])));
        $end_time =  date("H:i:s", strtotime( strtoupper($end[1])));
            $this->_bookingHelper->logDataInLogger("EventCalled ".$startDate.'T'.$start_time.'+00:00');
            $this->_bookingHelper->logDataInLogger("EventCalled ".$endDate.'T'.$end_time.'+00:00');
            $this->_bookingHelper->logDataInLogger("EventCalled ".'2021-03-07T17:06:02.000Z');
             $this->_bookingHelper->logDataInLogger("EventCalled ".strtoupper($start[1]));
            $this->_bookingHelper->logDataInLogger("EventCalled ".strtoupper($end[1]));
        $appointment =   [
            'summary' => $event_summary,
            'location' => $street_address,
            'description' => 'Booked appointment',
            'start' => [
                'dateTime' => $startDate.'T'.$start_time.'-04:00',
                'timeZone' => $timeZone,
            ],
            'end' => [
                'dateTime' => $endDate.'T'.$end_time.'-04:00',
                'timeZone' => $timeZone,
            ],
            'recurrence' => [
                'RRULE:FREQ=DAILY;COUNT=1'
            ],
            'attendees' => [
                ['email' => $this->customerSession->getCustomer()->getEmail()],
            ],
            'reminders' => [
                'useDefault' => FALSE,
                'overrides' => [
                    [ 'method' => 'email', 'minutes' => 24 * 60 ],
                    ['method' => 'popup', 'minutes' => 10],
                ],
            ],
        ];
        $event = new Google_Service_Calendar_Event($appointment);
        try {
            $service->events->insert(self::CALENDAR_ID, $event);
        } catch (Google_Service_Exception $exception) {
            echo $exception->getMessage();
        }
    }
    function getClient()
    {
        $client = new Google_Client();
        $applicationName = $this->scopeConfig->getValue(self::APPLICATION_NAME, $this->storeScope);
        $client->setApplicationName($applicationName);
        $client->setScopes(Google_Service_Calendar::CALENDAR);
        $client->setAuthConfig($this->getCredentials());
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        $base_url = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        if (file_exists(self::TOKEN_FILE)) {
            $accessToken = json_decode(file_get_contents(self::TOKEN_FILE), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname(self::TOKEN_FILE))) {
                mkdir(dirname(self::TOKEN_FILE), 0700, true);
            }
            file_put_contents(self::TOKEN_FILE, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function getCredentials(){

        if (!file_exists(self::CREDENTIAL_FILE)) {
            print 'Enter Client ID: ';
            $clientId = trim(fgets(STDIN));
            print 'Enter Project ID: ';
            $projectId = trim(fgets(STDIN));
            print 'Enter Client Secret: ';
            $clientSecret = trim(fgets(STDIN));
            $authUri = $this->scopeConfig->getValue(self::AUTHENTICATION_URI, $this->storeScope);
            $tokenUri = $this->scopeConfig->getValue(self::TOKEN_URI, $this->storeScope);
            $accessProvider = $this->scopeConfig->getValue(self::ACCESS_PROVIDER, $this->storeScope);
            $redirectUri = $this->scopeConfig->getValue(self::REDIRECT_URI, $this->storeScope);
            $credentials = [
                'web' => [
                    'client_id' => $clientId,
                    'project_id' => $projectId,
                    'auth_uri' => $authUri,
                    'token_uri' => $tokenUri,
                    'auth_provider_x509_cert_url' => $accessProvider,
                    'client_secret' => $clientSecret,
                    'redirect_uris' => [$redirectUri],
                ]
            ];
            $jsonCredentials = json_encode($credentials);
            $content = $jsonCredentials;
            file_put_contents(self::CREDENTIAL_FILE, $content, FILE_APPEND | LOCK_EX);
            return json_decode(file_get_contents(self::CREDENTIAL_FILE), true);
        }
        else {
            return json_decode(file_get_contents(self::CREDENTIAL_FILE), true);
        }

    }

}

