<?php

namespace InformaticsCommerce\RequestForQuote\Controller\Quote;

use InformaticsCommerce\RequestForQuote\Helper\Data;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Submit extends Action implements  HttpPostActionInterface
{
    protected $_jsonFactory;
    protected $_http;
    protected $_transportBuilder;
    protected $_storeManager;
    protected $_inlineTranslation;
    protected $_dataHelper;

    public function __construct(Context $context,  TransportBuilder      $transportBuilder,
                                StoreManagerInterface $storeManager,
                                StateInterface        $state,
                                Http                  $http,
                                JsonFactory           $jsonFactory,
                                Data                  $dataHelper
    )    {
        $this->_transportBuilder = $transportBuilder;
        $this->_storeManager = $storeManager;
        $this->_inlineTranslation = $state;
        $this->_http = $http;
        $this->_jsonFactory = $jsonFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context);
    }
    public function execute()
    {
        $params = $this->_http->getParams();

        $requiredFields = [];
        $attrValue = [];
        $result = $this->_jsonFactory->create();
        $productAttributes = $this->_http->getParam('super_attribute_modal');
        foreach ($productAttributes as $key => $attribute) {
            $options[$key] = $attribute;
            if ($attribute == '') {
                $requiredFields[] = $key;
            }
        }
        if (empty($requiredFields)) {


            $sku = $this->_http->getParam('product_sku');
            $url = $this->_http->getParam('product_url');
            $name = $this->_http->getParam('customername');
            $email = $this->_http->getParam('email');
            $question = $this->_http->getParam('question');
            $qty = $this->_http->getParam('package_type');

           // $productType = $this->_dataHelper->getProductBySku($sku)->getTypeId();

//            if ($productType === Configurable::TYPE_CODE) {
//                $product = $this->_dataHelper->getSelectedChildProductByOptions(
//                    $sku,
//                    $params['super_attribute_modal']
//                );
//            } else {
//                $product = $this->_dataHelper->getProductBySku($sku);
//            }

            foreach ($params['super_attribute_modal'] as $key => $paramAttr) {
                $attrValue[$key] = $paramAttr;
            }


            $templateVars = [
                'sku' => $sku,
                'product_url' => $url,
                'email' => $email,
                'name' => $name,
                'phonenumber' => $params['phonenumber'],
                'address' => $params['address'],
                'apparment' => $params['apparment'],
                'package_type' => $qty. ' LB',
                'city' => $params['city'],
                'state' => $params['state'],
                'zipcode' => $params['zipcode']
            ];
            foreach ($attrValue as $key => $d) {
                $templateVars[$key] = $d;
            }
            $storeId = $this->_storeManager->getStore()->getId();
            $from = ['email' => 'tariqsyed08@gmail.com', 'name' => 'Sales'];
            $this->_inlineTranslation->suspend();


            $templateOptions = [
                'area' => Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            try {
                $transport = $this->_transportBuilder->setTemplateIdentifier('request_quote_general_email_template', ScopeInterface::SCOPE_STORE)
                    ->setTemplateOptions($templateOptions)
                    ->setTemplateVars($templateVars)
                    ->setFrom($from)
                    ->addTo($email)
                    ->addCc($from)
                    ->setReplyTo('sales@example.com')
                    ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                $result->setData(['success' => true, "message" => __('Thank you for submitting quote.')]);
                return $result;
            } catch (LocalizedException $e) {
                $result->setData(['success' => false, "message" => __($e->getMessage())]);
                return $result;
            }
        } else {
            $response = ['success' => false, 'empty_fields' => $requiredFields];
            $result->setData($response);
            return $result;
        }
    }
}
