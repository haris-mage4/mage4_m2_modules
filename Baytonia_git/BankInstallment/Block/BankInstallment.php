<?php

namespace Baytonia\BankInstallment\Block;
use Baytonia\BankInstallment\Model\Status;
use Magento\Framework\Pricing\Helper\Data;

class BankInstallment extends \Magento\Framework\View\Element\Template
{
    /**
     * template for evolution bankinstallment.
     */
    const TEMPLATE = 'Baytonia_BankInstallment::bankinstallment/banklogo.phtml';
    const XML_CONFIG_ENABLE = 'bankinstallment/general/enable_frontend';

    /**
     * scope config.
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    
    /**
     * @var \Baytonia\BankInstallment\Model\BankRepository
     */
    protected $_bankRepository;
    
    /**
     * var \Magento\Framework\View\Asset\Repository
     */
    protected  $_assetRepo;

    /**
     * var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * var Data
     */
    protected $priceHelper;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo, 
        \Baytonia\BankInstallment\Model\BankRepository $bankRepository,
        \Magento\Framework\Registry $registry,
        Data $priceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_bankRepository = $bankRepository;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_assetRepo = $assetRepo;
        $this->_registry = $registry;
        $this->priceHelper = $priceHelper;
    }
    
    /**
     * @return
     */
    protected function _toHtml()
    {
        $store = $this->_storeManager->getStore()->getId();
        $configEnable = $this->_scopeConfig->getValue(
            self::XML_CONFIG_ENABLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        
        if ($configEnable && $this->_bankRepository->getBankCollection()->getSize()) {
            $this->setTemplate(self::TEMPLATE);
        }
        
        return parent::_toHtml();
    }
    
    /**
     * get bank collection of bankinstallment.
     *
     * @return \Baytonia\BankInstallment\Model\ResourceModel\Bank\Collection
     */
    public function getBankCollection()
    {
        return $this->_bankRepository->getBankCollection();
    }

    /**
     * get installment text
     *
     * @return string
     */
    public function getInstallmentText()
    {
        $store = $this->_storeManager->getStore()->getId();
        return $this->_scopeConfig->getValue(
            'bankinstallment/general/installment_text',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
    /**
     * get bank image url.
     *
     * @param \Baytonia\BankInstallment\Model\Bank $bank
     *
     * @return string
     */
    public function getBankImageUrl(\Baytonia\BankInstallment\Model\Bank $bank)
    {
        $srcImage = $this->getBaseUrlMedia($bank->getImage());
        if (!preg_match('~\.(png|gif|jpe?g|bmp)~i', $srcImage)) {
            $srcImage = $this->_assetRepo->getUrl("Baytonia_BankInstallment::images/bank-logo-blank.png");
        }
        return $srcImage;
    }

    /**
     * get flexslider html id.
     *
     * @return string
     */
    public function getBankLogoHtmlId()
    {
        return 'baytonia-bankinstallmen';
    }
    
    /**
     * get Base Url Media.
     *
     * @param string $path   [description]
     * @param bool   $secure [description]
     *
     * @return string [description]
     */
    public function getBaseUrlMedia($path = '', $secure = false)
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, $secure) . $path;
    }


    /**
     * get BankInstallment Bank Url
     * @return string
     */
    public function getBankInstallmentBankUrl()
    {
        return $this->_backendUrl->getUrl('*/*/banks', ['_current' => true]);
    }

    /**
     * get Backend Url
     * @param  string $route
     * @param  array  $params
     * @return string
     */
    public function getBackendUrl($route = '', $params = ['_current' => true])
    {
        return $this->_backendUrl->getUrl($route, $params);
    }

    /**
     * get getCurrentProduct Url
     */
    public function getCurrentProduct()
    {
        return $this->_registry->registry('current_product');
    }

    /**
     * get getCurrentProduct Url
     */
    public function getInstallmentPrice()
    {
        $store = $this->_storeManager->getStore()->getId();
        $installmentVar = $this->_scopeConfig->getValue(
            'bankinstallment/general/installment_price',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if(empty($installmentVar)){
            $installmentVar = 6;
        }

        $installmentPrice = $this->getCurrentProduct()->getFinalPrice() / $installmentVar;
        return $this->getFormattedPrice($installmentPrice);
    }
    
    /**
     * get getFormattedPrice Url
     */
    public function getFormattedPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }
}