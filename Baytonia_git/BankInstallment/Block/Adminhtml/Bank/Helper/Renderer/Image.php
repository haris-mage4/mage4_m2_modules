<?php

namespace Baytonia\BankInstallment\Block\Adminhtml\Bank\Helper\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    /**
     * Store manager.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * bank factory.
     *
     * @var \Baytonia\BankInstallment\Model\BankFactory
     */
    protected $_bankFactory;

    /**
     * image model
     *
     * @var \Baytonia\BankInstallment\Model\Bank\Image
     */
    protected $imageModel;
    protected $_assetRepo;

    /**
     * [__construct description].
     *
     * @param \Magento\Backend\Block\Context              $context
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Baytonia\BankInstallment\Model\BankFactory $bankFactory
     * @param array                                       $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Baytonia\BankInstallment\Model\BankFactory $bankFactory, 
        \Baytonia\BankInstallment\Model\Bank\Image $imageModel, 
        \Magento\Framework\View\Asset\Repository $assetRepo, 
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_bankFactory = $bankFactory;
        $this->imageModel = $imageModel;
        $this->_assetRepo = $assetRepo;
    }

    /**
     * Render action.
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row) {
        $storeViewId = $this->getRequest()->getParam('store');
        $bank = $this->_bankFactory->create()->setStoreViewId($storeViewId)->load($row->getId());
        
        if (preg_match('~\.(png|gif|jpe?g|bmp)~i', $bank->getImage())) {
            $srcImage = $this->imageModel->getBaseUrl() . $bank->getImage();
        } else {
            $srcImage = $this->_assetRepo->getUrl("Baytonia_BankInstallment::images/bank-logo-blank.png");
        }

        return '<image max-width="150" height="50" src ="' . $srcImage . '" alt="' . $bank->getAltText() . '" >';
    }

}
