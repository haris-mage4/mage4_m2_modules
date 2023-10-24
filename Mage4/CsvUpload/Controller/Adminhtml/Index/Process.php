<?php

namespace Mage4\CsvUpload\Controller\Adminhtml\Index;

use Mage4\CsvUpload\Model\Csv;
use Magento\Store\Model\StoreManagerInterface;
use Mage4\CsvUpload\Model\CsvRepository;
/**
 * Process controller class
 */
class Process extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $generic;

    /**
     * @var \Mage4\CsvUpload\Model\CsvFactory
     */
    private $csvFactory;

    protected $csvRepository;

    /**
     * Process constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\Generic $generic
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Mage4\CsvUpload\Model\CsvFactory $csvFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context              $context,
        \Magento\Framework\View\Result\PageFactory       $resultPageFactory,
        \Magento\Framework\Json\Helper\Data              $jsonHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory          $adapterFactory,
        \Magento\Framework\Filesystem                    $filesystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface       $storeManager,
        \Magento\Framework\Session\Generic               $generic,
        \Psr\Log\LoggerInterface                         $logger,
        \Mage4\CsvUpload\Model\CsvFactory                $csvFactory,
        CsvRepository $csvRepository
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->generic = $generic;
        $this->csvFactory = $csvFactory;
        $this->csvRepository = $csvRepository;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $fileSaved = false;
        $orignalfile = $_FILES ['import_csv_file']['name'];
        $newPricingArr = [];
        $productFile  = '';
        $data = [];
        try {
            $newFileName = $orignalfile;
            $mediaUrl = $this->mediaDirectory->getAbsolutePath('csv/');
            $uploader = $this->uploaderFactory->create(['fileId' => 'import_csv_file']);
            $file = $this->csvFactory->create();
            $collection = $file->getCollection(); //Get Collection of module data
            $collection->addFieldToFilter('filename',$newFileName);

                if (count($collection->getData()) > 0 ){
                    $fileSaved = true ;
//                    $this->messageManager->addError(__('File already Exists'));
                }else{
                    $data = [
                        'filename' => $newFileName,
                        'path' => $mediaUrl . $newFileName,
                    ];
                    $file->setFilename($newFileName);
                    $file->setFilePath($data['path']);
                    $uploader->setAllowedExtensions(['csv']);
                    $uploader->setAllowRenameFiles(false);
                    $file->save();
                    $fileSaved = true;
                    $this->messageManager->addSuccess(__('File Saved'));
                }
                if ($fileSaved){
                    /** file converted start */

                    $result = $uploader->save($mediaUrl, $newFileName);

                    if ($result['file']){
                        $productFile = $mediaUrl . $result['file'];
                        $priceMatrix = $this->readCSV($productFile);
                        $heightArr = $this->getFirstColumnHeight($priceMatrix);
                        $widthArr = $this->getFirstRowWidth($priceMatrix);
                        $pricingArr = $this->makePricingArray($priceMatrix);  // "heightxwidth"

                        $minHeight = min($heightArr);
                        $maxHeight = max($heightArr);
                        $minWidth = min($widthArr);
                        $maxWidth = max($widthArr);

                        $countHeight = 0;
                        $newPricingArr[0][0] = 'dimension';
                        for ($i = $minHeight; $i <= $maxHeight; $i++) {
                            $newPricingArr[$i][0] = $i;
                            if ($i == 30) {
                                $countHeight++;
                            } elseif ($i == 36) {
                                $countHeight++;
                            } elseif ($i == 42) {
                                $countHeight++;
                            } elseif ($i == 48) {
                                $countHeight++;
                            } elseif ($i == 54) {
                                $countHeight++;
                            } elseif ($i == 60) {
                                $countHeight++;
                            } elseif ($i == 66) {
                                $countHeight++;
                            } elseif ($i == 72) {
                                $countHeight++;
                            } elseif ($i == 78) {
                                $countHeight++;
                            } elseif ($i == 84) {
                                $countHeight++;
                            } elseif ($i == 96) {
                                $countHeight++;
                            } elseif ($i == 108) {
                                $countHeight++;
                            } elseif ($i == 120) {
                                $countHeight++;
                            }
                            for ($j = $minWidth; $j <= $maxWidth; $j++) {
                                $newPricingArr[0][$j] = $j;
                                $index = array_keys($pricingArr)[$countHeight];
                                if ($j < 18) {
                                    $newPricingArr[$i][$j] = $pricingArr[$index][12];
                                } elseif ($j < 24 && $j >= 18) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][18];
                                } elseif ($j < 30 && $j >= 24) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][24];
                                } elseif ($j < 36 && $j >= 30) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][30];
                                } elseif ($j < 42 && $j >= 36) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][36];
                                } elseif ($j < 48 && $j >= 42) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][42];
                                } elseif ($j < 54 && $j >= 48) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][48];
                                } elseif ($j < 60 && $j >= 54) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][54];
                                } elseif ($j < 66 && $j >= 60) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][60];
                                } elseif ($j < 72 && $j >= 66) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][66];
                                } elseif ($j < 78 && $j >= 72) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][72];
                                } elseif ($j < 84 && $j >= 78) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][78];
                                } elseif ($j < 96 && $j >= 84) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][84];
                                } elseif ($j < 108 && $j >= 96) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][96];
                                } elseif ($j < 120 && $j >= 108) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][108];
                                } elseif ($j == 120) {
                                    $index = array_keys($pricingArr)[$countHeight];
                                    $newPricingArr[$i][$j] = $pricingArr[$index][120];
                                }
                            }
                        }
                        /** File converted End */

                        /** remove dollar sign from sheet */
                        $data = $newPricingArr;
                        foreach ($data as &$row) {
                            $row = preg_replace('/[^\d\.]/', "", $row);
                        }
                        $data[0][0] = 'dimensions';
                        $this->convertArray2CSV($data, $productFile);
                        /** remove dollar sign from sheet  - End */
                        $this->messageManager->addSuccess(__('File modified'));
                    }

                }

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->messageManager->addError(__($message));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        try {
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    function convertArray2CSV($newPricingArray, $csvName)
    {
        $fp = fopen($csvName, 'w+');
        foreach ($newPricingArray as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);
    }

    function makePricingArray($priceMatrix)
    {
        $pricing = [];
        $height = '';
        foreach ($priceMatrix as $i => $price) {
            foreach ($price as $ind => $val) {
                if ($ind == 'height') {
                    $height = $val;
                } else {
                    $pricing[$height][$ind] = $val;

                }
            }
        }
        return $pricing;
    }

    function getFirstRowWidth($priceMatrix)
    {
        $width = array_keys($priceMatrix[0]);
        $firstElement = array_shift($width); // remove first element heading
        return $width;
    }

    function getFirstColumnHeight($priceMatrix)
    {
        $height = [];
        foreach ($priceMatrix as $row) {
            $height[] = $row['height'];
        }
        return $height;
    }

    function readCSV($fileName)
    {

        $rows = array_map('str_getcsv', file($fileName));
        $header = array_shift($rows);
        $csv = array();
        foreach ($rows as $row) {
            $csv[] = array_combine($header, $row);

        }

        return $csv;
        fclose($fileName);
    }
}





