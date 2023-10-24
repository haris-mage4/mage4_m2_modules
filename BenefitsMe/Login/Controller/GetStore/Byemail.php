<?php

namespace BenefitsMe\Login\Controller\Getstore;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\ResourceConnection;

class Byemail extends Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        protected ResourceConnection $resource
    ) {
        parent::__construct($context);
        $this->_resource = $resource;
    }

    public function execute()
    {
        $email = $this->getRequest()->getParam("email");

        $finalStoreCode = "default";

        $query = "select code from store where website_id = (select website_id from customer_entity where email = '".$email."');";

        $connection = $this->resource->getConnection();
        $result = $connection->fetchAll($query);

        if(count($result)) {
            $finalStoreCode = $result[0]['code'];
        }

        return $this->getResponse()->setBody($finalStoreCode);
    }
}

