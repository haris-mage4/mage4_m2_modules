<?php
namespace Baytonia\OutOfStockFix\Plugin;

class NotifyStockAlert
{
    protected $alertHelper;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;
    public function __construct(\Magento\ProductAlert\Helper\Data $alertHelper,\Magento\Framework\App\Http\Context $httpContext)
    {
        $this->alertHelper = $alertHelper;
        $this->httpContext = $httpContext;
    }
    public function aroundSetTemplate(\Magento\ProductAlert\Block\Product\View\Stock $subject, callable $proceed, $template)
    {
        $template = "Baytonia_OutOfStockFix::product/view.phtml";
        
		 $result = $proceed($template);
         if(!$this->isLoggedIn()){
            $subject->setSignupUrl("");
         }
         


		return $result;
    }
     public function isLoggedIn()
    {
        $isLoggedIn = $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
        return $isLoggedIn;
    }
}
