<?php
 
namespace Baytonia\Customproductgridfield\Ui\Component\Listing\Column;
 
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

 
class Managestock extends \Magento\Ui\Component\Listing\Columns\Column
{
   
   public function prepareDataSource(array $dataSource)
   {
      $fieldName = $this->getData('name');
      if (isset($dataSource['data']['items'])) {
         foreach ($dataSource['data']['items'] as & $item) {
            
            $productId=$item['entity_id'];
            $productsku=$item['sku'];
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();

            $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
            $connection = $resource->getConnection();
            
            $query = "SELECT metadata FROM `inventory_reservation` WHERE sku='".$productsku."';";
            $result1 = $connection->fetchAll($query);
            
            $datacollection = array();
            foreach ($result1 as $single):

               $split = explode(":", $single["metadata"]);
               $object_id = str_replace('"',"",str_replace('}', "", end($split)));
               $event_type = str_replace('"',"",str_replace('","object_type"', "", $split[1]));

               if(strstr($single["metadata"], "order_placed")){

                  $query1 = "SELECT increment_id FROM sales_order WHERE entity_id =".$object_id.";";
                  $result2 = $connection->fetchAll($query1);

                  // $datacollection[] = print_r($result2, 0);
                  if(isset($result2[0]['increment_id'])):
                     $datacollection[] = $event_type.":".$result2[0]['increment_id'];
                  endif;

               }

            endforeach;
            
            $datacollection = array_unique($datacollection);
            $item[$fieldName]=implode(',', $datacollection);
         
         }
      }
   return $dataSource;
   }
}