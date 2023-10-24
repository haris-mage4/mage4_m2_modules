<?php

namespace InformaticsCommerce\ShippingAddressResidential\Model\Order\Pdf;

use Magento\Store\Model\App\Emulation;
use Magento\Sales\Model\Order\Pdf\Config;
use Magento\Sales\Model\Order\Pdf\Total\Factory;
use Magento\Sales\Model\Order\Pdf\ItemsFactory;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Sales\Model\RtlTextHandler;

class PrintInvoice extends \InformaticsCommerce\ShippingAddressResidential\Model\Order\Pdf\PrintPdf
{
    protected $appEmulation;
    protected $_storeManager;

    public function __construct( \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Store\Model\App\Emulation $appEmulation, \Magento\Payment\Helper\Data $paymentData, \Magento\Framework\Stdlib\StringUtils $string, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Filesystem $filesystem, Config $pdfConfig, Factory $pdfTotalFactory, ItemsFactory $pdfItemsFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation, \Magento\Sales\Model\Order\Address\Renderer $addressRenderer, array $data = [], Database $fileStorageDatabase = null, ?RtlTextHandler $rtlTextHandler = null)
    {
        $this->appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
        parent::__construct($paymentData, $string, $scopeConfig, $filesystem, $pdfConfig, $pdfTotalFactory, $pdfItemsFactory, $localeDate, $inlineTranslation, $addressRenderer, $data, $fileStorageDatabase, $rtlTextHandler);
    }


   public function getPdf($invoices = [])
   {
       $this->_beforeGetPdf();
       $this->_initRenderer('invoice');

       $pdf = new \Zend_Pdf();
       $this->_setPdf($pdf);
       $style = new \Zend_Pdf_Style();
       $this->_setFontBold($style, 10);

       foreach ($invoices as $invoice) {
           if ($invoice->getStoreId()) {
               $this->appEmulation->startEnvironmentEmulation(
                   $invoice->getStoreId(),
                   \Magento\Framework\App\Area::AREA_FRONTEND,
                   true
               );
               $this->_storeManager->setCurrentStore($invoice->getStoreId());
           }
           $page = $this->newPage();
           $order = $invoice->getOrder();
           /* Add image */
           $this->insertLogo($page, $invoice->getStore());
           /* Add address */
           $this->insertAddress($page, $invoice->getStore());
           /* Add head */
           $this->insertOrder(
               $page,
               $order,
               $this->_scopeConfig->isSetFlag(
                   self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                   \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                   $order->getStoreId()
               )
           );
           /* Add document text and number */
           $this->insertDocumentNumber($page, __('Invoice # ') . $invoice->getIncrementId());
           /* Add table */
           $this->_drawHeader($page);
           /* Add body */
           foreach ($invoice->getAllItems() as $item) {
               if ($item->getOrderItem()->getParentItem()) {
                   continue;
               }
               /* Draw item */
               $this->_drawItem($item, $page, $order);
               $page = end($pdf->pages);
           }
           /* Add totals */
           $this->insertTotals($page, $invoice);
           if ($invoice->getStoreId()) {
               $this->appEmulation->stopEnvironmentEmulation();
           }
       }
       $this->_afterGetPdf();
       return $pdf;
   }
    /**
     * Draw header for item table
     *
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function _drawHeader(\Zend_Pdf_Page $page)
    {
        /* Add table head */
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_Rgb(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Products'), 'feed' => 35];

        $lines[0][] = ['text' => __('SKU'), 'feed' => 290, 'align' => 'right'];

        $lines[0][] = ['text' => __('Qty'), 'feed' => 435, 'align' => 'right'];

        $lines[0][] = ['text' => __('Price'), 'feed' => 360, 'align' => 'right'];

        $lines[0][] = ['text' => __('Tax'), 'feed' => 495, 'align' => 'right'];

        $lines[0][] = ['text' => __('Subtotal'), 'feed' => 565, 'align' => 'right'];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }
}
