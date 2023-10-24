<?php

namespace Baytonia\WebkulOdooExtended\Helper;
 
use Webkul\Odoomagentoconnect\Helper\Connection as WebkulOdooHelperConnection;
use Baytonia\WebkulOdooExtended\Logger\Logger;
use Magento\Framework\App\ObjectManager;
 
class Connection extends WebkulOdooHelperConnection
{
   public function addError($data, $file_name='odoo_connector.log')
   {
        $logger = ObjectManager::getInstance()->get(Logger::class);
        $logger->info($data);
   }
}
