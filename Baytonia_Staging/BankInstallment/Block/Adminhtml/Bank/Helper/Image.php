<?php
namespace Baytonia\BankInstallment\Block\Adminhtml\Bank\Helper;
use Magento\Framework\Data\Form\Element\Image as ImageField;
use Magento\Framework\Data\Form\Element\Factory as ElementFactory;
use Magento\Framework\Data\Form\Element\CollectionFactory as ElementCollectionFactory;
use Magento\Framework\Escaper;
use Baytonia\BankInstallment\Model\Bank\Image as BankImage;
use Magento\Framework\UrlInterface;

/**
 * @method string getValue()
 */
class Image extends ImageField
{
    /**
     * image model
     *
     * @var \Baytonia\BankInstallment\Model\Bank\Image
     */
    protected $imageModel;

    /**
     * @param BankImage $imageModel
     * @param ElementFactory $factoryElement
     * @param ElementCollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        BankImage $imageModel,
        ElementFactory $factoryElement,
        ElementCollectionFactory $factoryCollection,
        Escaper $escaper,
        UrlInterface $urlBuilder,
        \Magento\Framework\View\Asset\Repository $assetRepo, 
        $data = []
    )
    {
        $this->imageModel = $imageModel;
        $this->_assetRepo = $assetRepo;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $urlBuilder, $data);
    }
    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        //valid image
        if ($this->getValue() && preg_match('~\.(png|gif|jpe?g|bmp)~i', $this->getValue())) {
            $url = $this->imageModel->getBaseUrl().$this->getValue();
        } else {
            $url = $this->_assetRepo->getUrl("Baytonia_BankInstallment::images/bank-logo-blank.png");
        }
        return $url;
    }
}