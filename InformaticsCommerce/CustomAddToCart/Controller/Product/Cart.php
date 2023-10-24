<?php

namespace InformaticsCommerce\CustomAddToCart\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as AddToCart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartManagementInterface;


/**
 *
 */
class Cart extends Action implements HttpPostActionInterface
{
    /**
     * @var CartManagementInterface
     */
    protected $cartManagement;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var JsonFactory
     */
    protected $_jsonFactory;
    /**
     * @var Http
     */
    protected $_httpRequest;
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var AddToCart
     */
    protected $_cart;

    /**
     * @param AddToCart $cart
     * @param Session $session
     * @param CartManagementInterface $cartManagement
     * @param ProductRepositoryInterface $productRepository
     * @param Http $httpRequest
     * @param JsonFactory $jsonFactory
     * @param Context $context
     */
    public function __construct(
        AddToCart                  $cart,
        Session $session,
        CartManagementInterface $cartManagement,
                                ProductRepositoryInterface $productRepository,
        Http $httpRequest,
        JsonFactory $jsonFactory,
        Context $context
    )    {
        $this->session = $session;
        $this->cartManagement = $cartManagement;
        $this->productRepository = $productRepository;
        $this->_httpRequest = $httpRequest;
        $this->_jsonFactory = $jsonFactory;
        $this->_cart = $cart;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $options = [];
        $requiredFields = [];
        $result = $this->_jsonFactory->create();
        $params = $this->_httpRequest->getParams();
        $qty = $this->_httpRequest->getParam('qty');
        $productId = $this->_httpRequest->getParam('product_id');
        $product = $this->productRepository->getById($productId);
        if ($product->getTypeId() !== 'configurable') {
            throw new LocalizedException(__('The product is not configurable.'));
        }
        foreach ($params['super_attribute'] as $key => $param) {
            $options[$key] = $param;
            if ($param == "") {
                $requiredFields[] = $key;
            }
        }
        if (empty($requiredFields)) {
            $params = array(
                'product' => $product->getId(),
                'super_attribute' => $options,
                'qty' => $qty
            );
            try {
                $this->_cart->addProduct($product->getId(), $params);
                $this->_cart->save();
                $response = ['error' => false];
                $result->setData($response);
                return $result;
            } catch (LocalizedException $exception) {
                $response = ['error' => true, 'message' => $exception->getMessage()];
                $result->setData($response);
                return $result;
            }
        } else {
            $response = ['error' => true, 'empty_fields' => $requiredFields];
            $result->setData($response);
            return $result;
        }
    }
}
