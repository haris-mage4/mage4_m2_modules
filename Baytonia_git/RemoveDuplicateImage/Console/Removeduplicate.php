<?php

namespace Baytonia\RemoveDuplicateImage\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Removeduplicate extends Command
{
   protected function configure()
   {
       $this->setName('duplicate:remove');
       $this->setDescription('Remove duplicate images from product');
       
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $app_state = $objectManager->get('\Magento\Framework\App\State');
        $app_state->setAreaCode('global');
        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0); 
        set_time_limit(0);
        $mediaApi = $objectManager->create('\Magento\Catalog\Model\Product\Gallery\Processor');
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $storeManager->setCurrentStore(0);
        $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $directoryList=$objectManager->get('\Magento\Framework\App\Filesystem\DirectoryList');
        $path = $directoryList->getPath('media'); 
        $productCollection=$objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $_products = $productCollection->addAttributeToSelect('*')->load();

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $catalog_product_entity_varchar = $resource->getTableName('catalog_product_entity_varchar');
        $catalog_product_entity_media_gallery = $resource->getTableName('catalog_product_entity_media_gallery');
       
        $productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
        $i =0;
        $total = count($_products);
        $count = 0;
        $productCount = array();
        $removeImage = 0;
        foreach($_products as $_prod) {
            $removeImage = 0;
            $_product = $productRepository->getById($_prod->getId());
            $_product->setStoreId(0);
            $_md5_values = array();
            $getDupImages = array();
            $base_image_main = $_product->getImage();
            $base_image = $_product->getImage();
            $base_image = current(explode(".", $base_image));

            if($base_image != 'no_selection') {
                $mediaUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                $filepath = $path.'/catalog/product' . $base_image ;
                if (file_exists($filepath)) {
                    $_md5_values[] = md5(file_get_contents($filepath));            
                }
                $i++;

                $getDuplicateImagesBase = preg_split('~_(?=[^_]*$)~', $base_image);
                if(isset($getDuplicateImagesBase[0])){
                    $getDupImages[] = $base_image;
                }

                echo "\r\n processing product ".$i." of $total ";
                // Loop through product images
                $gallery = $_product->getMediaGalleryEntries();
                if ($gallery) {
                    foreach ($gallery as $key => $galleryImage) {
                        //protected base image
                        
                        if($galleryImage->getFile() == $base_image_main) {
                            continue;
                        }

                        $galleryImageC = current(explode(".", $galleryImage->getFile()));
                        $getDuplicateImages = preg_split('~_(?=[^_]*$)~', $galleryImageC);
                        
                        
                        if(isset($getDuplicateImages[0])){
                            if(!empty($getDupImages)){
                                $exist = 0;
                                foreach($getDupImages as $index => $string) {
                                    if (strpos($string, $getDuplicateImages[0]) !== FALSE){
                                        $exist = 1;
                                        break;
                                    }
                                }
                            }   
                           if(!in_array($getDuplicateImages[0], $getDupImages) && $exist == 0){
                              $getDupImages[] = $galleryImageC;
                           }else{
                              $removeImage++;
                              unset($gallery[$key]);
                            $whereConditions = [
                                  $connection->quoteInto('value = ?', $galleryImage->getFile()),
                                  $connection->quoteInto('entity_id = ?', $_prod->getId())
                            ];
                            $connection->delete($catalog_product_entity_varchar, $whereConditions);
                            $whereConditionsMedia = [
                                  $connection->quoteInto('value = ?', $galleryImage->getFile()),
                            ];
                            $connection->delete($catalog_product_entity_media_gallery, $whereConditionsMedia);
                              echo "\r\n removed duplicate image from ".$_product->getSku();
                              $count++;
                           }
                        }
                    }  
                }
              }
            }
               $output->writeln("\r\n Duplicate Images are Removed");
           }
}
