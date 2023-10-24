<?php

namespace CapitalOne\Flexpay\Model;

class Payment extends \Magento\Payment\Model\Method\Cc
{
    const METHOD_CODE = 'Capitalone';

    protected $_code                     = self::METHOD_CODE;

    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_minOrderTotal = 0;
    protected $_supportedCurrencyCodes = ['USD','GBP','EUR'];

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory, \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Payment\Model\Method\Logger $logger, \Magento\Framework\Module\ModuleListInterface $moduleList, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null, \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null, array $data = [])
    {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $paymentData, $scopeConfig, $logger, $moduleList, $localeDate, $resource, $resourceCollection, $data);
    }
}
