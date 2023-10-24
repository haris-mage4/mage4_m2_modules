<?php
namespace Baytonia\CustomerOdooTicket\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
   protected $scopeConfig;

   /**
   * Recipient email config path
   */
   const XML_PATH_URL       = 'submit_ticket/general_settings/url';
   const XML_PATH_ENABLED   = 'submit_ticket/general_settings/enabled';

   public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
   {
      $this->scopeConfig = $scopeConfig;
   }
   
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    public function isEnabled(){
        return $this->getConfig(self::XML_PATH_ENABLED);
    }
    
    public function getOdooUrl(){
        return $this->getConfig(self::XML_PATH_URL);
    }
    
    public function getSubmitUrl($_order){
        $data = array(
            'person_name' => trim($_order->getCustomerFirstname() . " " . $_order->getCustomerLastname()),
            'email' => $_order->getCustomerEmail(),
            'sale_order_id' => $_order->getIncrementId(),
            'customer_number' => $_order->getCustomerId()
        );

        $baseurl = $this->getOdooUrl() .  "support/ticket/submit?".http_build_query($data);
        return $baseurl;
    }
}