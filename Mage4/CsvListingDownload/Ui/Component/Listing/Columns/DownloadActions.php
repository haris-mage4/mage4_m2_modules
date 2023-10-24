<?php
namespace Mage4\CsvListingDownload\Ui\Component\Listing\Columns;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;


class DownloadActions extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /** Url Path */
    const NAME ='Download';
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = array(),
        UrlInterface $urlBuilder,
        array $data = array())
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $mediaUrl =  $objectManager->get('Magento\Store\Model\StoreManagerInterface')
                                    ->getStore()
                                    ->getBaseUrl();
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');

                if (isset($item['file_path'])) {
                    $str_arr = explode ("pub",$item['file_path']);

                    $linkDownload = $mediaUrl.'pub'.$str_arr[1];

                    $item[$name] = '<a class="csv_download" href="'.$linkDownload.'"target="_blank">' . __('Download') . '</a>';

                }
            }
        }

        return $dataSource;

    }
}
?>


