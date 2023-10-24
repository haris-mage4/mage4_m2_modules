<?php

namespace Mage4\OrderGrid\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Sales\Model\Order;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class MerchantAccountTab extends \Magento\Backend\Block\Template implements TabInterface
{
    protected $_template = 'order/view/tab/merch_acc.phtml';
    private $_coreRegistry;
    private UrlInterface $urlBuilder;

    public function __construct(Context $context, Registry $registry, UrlInterface $urlBuilder, array $data = []) {
        $this->_coreRegistry = $registry;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $data);
    }

    private function getOrder(): Order
    {
        return $this->_coreRegistry->registry('current_order');
    }

    public function getOrderId(): int
    {
        return $this->getOrder()->getEntityId();
    }

    public function formSubmitUrl(): string
    {
        return $this->urlBuilder->getUrl('merch_acc/sales_order/saveAction');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Merchant Account');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Merchant Account');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    public function _toHtml()
    {
        if (!empty($this->getOrder()->getData('merchant_account'))) {
            return '
            <div class="admin__page-section-item-content">
                <table class="admin__table-secondary order-account-information-table">
                    <tbody><tr style="font-weight: bold;">
                        <th>Merchant Account Name</th>
                        <td>'. $this->getOrder()->getData('merchant_account') .'</td>
                    </tr></tbody></table>
            </div>';
        }
        return parent::_toHtml();
    }
}
