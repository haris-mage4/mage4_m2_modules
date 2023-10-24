<?php
namespace Baytonia\Product\Ui\Component\Listing\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
class ReserveQuantity extends Column
{
    /**
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {

                $productId=$item['entity_id'];
                $productsku=$item['sku'];

                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                
                $query = "SELECT metadata, reservation_id FROM `inventory_reservation` WHERE sku='".$productsku."';";
                $result1 = $connection->fetchAll($query);
                
                $datacollection = array();
                foreach ($result1 as $single):
    
                   $split = explode(":", $single["metadata"]);
                   $object_id = str_replace('"',"",str_replace('}', "", end($split)));

                  // print_r($object_id); die('ss');
    
                   if(strstr($single["metadata"], "order_placed")){

                   $datacollection[] = $object_id;

                   // $item[$this->getData('name')] = $object_id;
    
                        $sql = "SELECT sku, SUM(quantity) as quantity  FROM `inventory_reservation` WHERE sku = '".$item['sku']."'   GROUP BY sku";
                        $result = $connection->fetchAll($sql); 
                        foreach ($result as $key => $value) {
                            $item[$this->getData('name')] = $value['quantity'];
                        }
                   }
                endforeach;
            //     $datacollection = array_unique($datacollection);
            //  $item[$this->getData('name')]=implode(',', $datacollection);
            }
        }
        return $dataSource;
    }
}
?>