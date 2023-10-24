<?php

namespace BenefitsMe\Employer\Plugin;

use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class OnePageSuccessPlugin
{

    public function __construct(
        protected LoggerInterface $_logger,
        protected Session $_customerSession,
        protected Order $_order,
        protected ResourceConnection $_resource,
    ) { }

    public function log($text) {
        $this->_logger->info($text);
    }

    public function afterExecute(
        \Magento\Checkout\Controller\Onepage\Success $subject,
        $result
    ) {
        // Your code here
        $currentCustomerId = $this->_customerSession->getCustomer()->getId();

        $order = $this->_order->getCollection()->addFieldToFilter('customer_id', $currentCustomerId)->setOrder('created_at','DESC')->getFirstItem();
        $orderId = $order->getId();

        $terms = $_COOKIE['terms'];
        $risa = $_COOKIE['risa'];

        foreach(explode("&", parse_url($risa, PHP_URL_QUERY)) as $item) {
            list ($key, $value) = explode("=", $item);
            $$key = $value;
        }

        $storeCreditAmount = $total_cost;

        $numPayments = 6;

        switch($terms) {
            case "6months":
                $numPayments = 12;
                break;
            case "9months":
                $numPayments = 18;
                break;
            case "12months":
                $numPayments = 26;
                break;
        }

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseURL = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $risa = str_replace(" ", "%20", $risa);
        $risa = $baseURL.$risa;

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.pdfshift.io/v3/convert/pdf",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(array("source" => $risa, "landscape" => false, "use_print" => true)),
            CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
            CURLOPT_USERPWD => 'api:160fb578cee44e2394d6764db76b263b'
        ));

        $response = curl_exec($curl);
        file_put_contents(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))."/pub/media/pdfs/".$order->getId().'_risa.pdf', $response);

        $customAttributes = [
            'payroll_deduction' => $terms,
            'risa_pdf' => '/media/pdfs/'.$order->getId().'_risa.pdf',
            'payroll_deduction_total' => $storeCreditAmount,
        ];

        foreach ($customAttributes as $attributeCode => $attributeValue) {
            $order->setData($attributeCode, $attributeValue);
        }

        $order->save();
        
        $x = number_format($storeCreditAmount / $numPayments, 2);
        
        $a = $x * ($numPayments - 1);
        $b = $storeCreditAmount - $a;

        $connection = $this->_resource->getConnection();
        
        $sql = "insert into agreements (order_id, term, total, payments, final_payment, date_created)
            values(".$order->getId().",'".$terms."',".$storeCreditAmount.",".$x.",".$b.",now());";    
        $data = $connection->fetchAll($sql);
        
        $agreementId = $connection->lastInsertId('agreements');
        
        $sql = "SELECT date FROM employer_paydays WHERE date > DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY date ASC LIMIT ".$numPayments.";";
        $data = $connection->fetchAll($sql);
        
        $count = 0;
        foreach($data as $item) {
            $count++;
            $date = $item['date'];
            $thePayment = $x;
            if($count == $numPayments) {
                $thePayment = $b;
            }
            $sql = "insert into ledger (agreement_id, amount, date, status) values(".$agreementId.",".$thePayment.",'".$date."','pending');";
            $connection->fetchAll($sql);
        }

        // Remove the cookies
        setcookie("terms", "", time() - 3600, '/');
        setcookie("risa", "", time() - 3600, '/');

        $this->log($orderId);
        return $result;
    }
}