<?php
 namespace Baytonia\OutOfStockFix\Plugin;

 class InjectAlertGrid {

 protected $_request;

 public function __construct(\Magento\Framework\App\RequestInterface $request,\Magento\Framework\View\LayoutFactory $layoutFactory){
     $this->_request = $request;
     $this->layoutFactory = $layoutFactory;
 }

 public function afterModifyMeta(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Alerts $alerts, $result){
    
    if(isset($result["alerts"]) && isset($result["alerts"]["children"])){
        $result["alerts"]["children"]["stock_guest"] = $this->getAlertStockFieldset($alerts);
    }
    
    return $result;
 }
 
 /**
     * Prepares config for the alert stock products fieldset
     * @return array
     */
    private function getAlertStockFieldset($alerts)
    {
        return [
            'arguments' => [
                'data' => [
                    'config' => [
                        'label' => __('Alert stock For Guest'),
                        'componentType' => 'container',
                        'component' => 'Magento_Ui/js/form/components/html',
                        'additionalClasses' => 'admin__fieldset-note',
                        'content' =>
                            '<h4>' . __('Alert Stock For Guest') . '</h4>' .
                            $this->layoutFactory->create()->createBlock(
                                \Baytonia\OutOfStockFix\Block\Adminhtml\Product\Edit\Tab\Alerts\Stock::class
                            )->toHtml(),
                    ]
                ]
            ]
        ];
    }
}