<?php
namespace Baytonia\CartUpdate\Model\Total;

class Originalsubtotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
   /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    protected $quoteValidator = null; 

    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator)
    {
        $this->quoteValidator = $quoteValidator;
    }
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array|null
     */
    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        $allitems = $quote->getAllVisibleItems();
        $subtatle = 0;
        
        foreach($allitems as $tt){
            $subtatle = $subtatle + ($tt->getProduct()->getPrice() * $tt->getQty());
        }
        
        if(round($subtatle,2) == round($total->getSubtotalInclTax(),2)){
            $subtatle = 0;
        }
        return [
            'code' => 'originalsubtotal',
            'title' => 'Original Subtotal',
            'value' => $subtatle
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Original Subtotal');
    }
}