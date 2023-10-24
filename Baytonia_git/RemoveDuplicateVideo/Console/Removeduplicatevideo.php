<?php

namespace Baytonia\RemoveDuplicateVideo\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Removeduplicatevideo extends Command
{
   protected function configure()
   {
       $this->setName('duplicatevideo:remove');
       $this->setDescription('Remove duplicate videos from product');
       
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $productCollection=$objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $_products = $productCollection->addAttributeToSelect('*')->load();
        $lizardmedia_product_attachment = $resource->getTableName('lizardmedia_product_attachment');
        $videoArray = array();

        foreach($_products as $_prod) {
            $videoArray = array();
            $product_id = $_prod->getId();
            $selectVideo = $connection->select()
                    ->from(
                        ['lizardmedia_attach' => $lizardmedia_product_attachment],
                        ['*']
                    )->where(
                        "lizardmedia_attach.product_id = :product_id"
                    );
            $bindVideo = ['product_id'=>$product_id];
            $attachmentVideos = $connection->fetchAll($selectVideo, $bindVideo);
            foreach ($attachmentVideos as $attachmentVideokey => $attachmentVideo) {
                echo "\r\n processing product id ".$attachmentVideo['product_id'];
                if (!in_array($attachmentVideo['attachment_url'], $videoArray)) {
                    $videoArray[] = $attachmentVideo['attachment_url'];
                }else{
                    $whereConditionsAttachment = [
                          $connection->quoteInto('id = ?', $attachmentVideo['id']),
                    ];
                    $connection->delete($lizardmedia_product_attachment, $whereConditionsAttachment);
                    echo "\r\n removed duplicate video from ".$attachmentVideo['product_id'];
                }
            }
        }
        $output->writeln("\r\n Duplicate video are Removed");
     }
}
