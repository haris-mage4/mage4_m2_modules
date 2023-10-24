<?php

namespace Mage4\ExtendMageWorxOptionTemplates\Model\Option\Templates;

use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory;
use Magento\Catalog\Api\ProductCustomOptionRepositoryInterface as OptionRepository;
use Magento\Catalog\Model\ProductOptions\ConfigInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\Product\ReadHandler;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use MageWorx\OptionBase\Helper\Data as BaseHelper;
use MageWorx\OptionBase\Model\AttributeSaver;
use MageWorx\OptionBase\Model\Entity\Group;
use MageWorx\OptionBase\Model\Entity\Product;
use MageWorx\OptionBase\Model\OptionSaver\Option as OptionDataCollector;
use MageWorx\OptionBase\Model\ResourceModel\CollectionUpdaterRegistry;
use MageWorx\OptionBase\Model\ResourceModel\DataSaver;
use MageWorx\OptionBase\Model\ResourceModel\Option as MageworxOptionResource;
use MageWorx\OptionTemplates\Model\GroupFactory;
use MageWorx\OptionTemplates\Helper\Data as Helper;
use MageWorx\OptionTemplates\Model\ResourceModel\Product as ResourceModelProduct;
use MageWorx\OptionTemplates\Model\Group\Source\SystemAttributes;
use Psr\Log\LoggerInterface;

class OptionSaver extends  \MageWorx\OptionTemplates\Model\OptionSaver {
   public function __construct(
       ReadHandler $readHandler,
       ConfigInterface $productOptionConfig,
       GroupFactory $groupFactory,
       CollectionFactory $productCollectionFactory,
       ProductCustomOptionInterfaceFactory $customOptionFactory,
       OptionRepository $optionRepository,
       ProductRepository $productRepository,
       LoggerInterface $logger,
       Helper $helper, BaseHelper $baseHelper,
       SystemAttributes $systemAttributes,
       Group $groupEntity,
       Product $productEntity,
       ManagerInterface $eventManager,
       CollectionUpdaterRegistry $collectionUpdaterRegistry,
       ResourceConnection $resource,
       ResourceModelProduct $resourceModelProduct,
       OptionDataCollector $optionDataCollector,
       AttributeSaver $attributeSaver,
       MageworxOptionResource $mageworxOptionResource,
       DataSaver $dataSaver,
       StoreManager $storeManager
   )   {
       parent::__construct($readHandler, $productOptionConfig, $groupFactory, $productCollectionFactory, $customOptionFactory, $optionRepository, $productRepository, $logger, $helper, $baseHelper, $systemAttributes, $groupEntity, $productEntity, $eventManager, $collectionUpdaterRegistry, $resource, $resourceModelProduct, $optionDataCollector, $attributeSaver, $mageworxOptionResource, $dataSaver, $storeManager);
   }

    public function addNewOptionProcess(array $productOptions, $group = null, $productSku)
    {
        if ($group === null) {
            $groupOptions = $this->groupOptions;
        } else {
            $groupOptions = $this->groupEntity->getOptionsAsArray($group);
        }

        foreach ($groupOptions as $groupOption) {
            $issetGroupOptionInProduct = false;
            foreach ($productOptions as $optionIndex => &$productOption) {
                if (empty($productOption['group_option_id'])
                    || $productOption['group_option_id'] !== $groupOption['option_id']
                ) {
                    continue;
                }

                $issetGroupOptionInProduct = true;
                if (isset($groupOption['dependency'])) {
                    $this->attributeSaver->addNewGroupOptionIds($groupOption['option_id']);
                    $productOption['dependency']                 = $groupOption['dependency'];
                    $productOption['need_to_process_dependency'] = true;
                }

                if (empty($productOption['values']) || !is_array($productOption['values'])
                    || empty($groupOption['values']) || !is_array($groupOption['values'])
                ) {
                    continue;
                }

                foreach ($productOption['values'] as &$productOptionValue) {
                    foreach ($groupOption['values'] as $groupOptionValue) {
                        if (empty($productOptionValue['group_option_value_id'])
                            || $productOptionValue['group_option_value_id'] !== $groupOptionValue['option_type_id']
                            || !isset($groupOptionValue['dependency'])
                        ) {
                            continue;
                        }
                        $productOptionValue['dependency']                 = $groupOptionValue['dependency'];
                        $productOptionValue['need_to_process_dependency'] = true;

                        $productOptionValue['is_default'] = $this->setIsDefaultAttrForLLPLogic($productSku, $productOptionValue);

                        $this->attributeSaver->addNewGroupOptionIds($groupOption['option_id']);
                    }
                }
            }

            if (!$issetGroupOptionInProduct) {
                $groupOption['group_option_id'] = $groupOption['id'];
                if ($this->getIsTemplateSave()) {
                    $groupOption['id']                   = $this->currentIncrementIds['option'];
                    $groupOption['option_id']            = $groupOption['id'];
                    $this->currentIncrementIds['option'] += 1;
                } else {
                    $groupOption['id']        = null;
                    $groupOption['option_id'] = null;
                }
                $this->attributeSaver->addNewGroupOptionIds($groupOption['group_option_id']);
                $groupOption['need_to_process_dependency'] = true;
                if ($groupOption['values']){
                    foreach ($groupOption['values'] as &$groupOptionValue) {
                        $groupOptionValue['is_default'] = $this->setIsDefaultAttrForLLPLogic($productSku, $groupOptionValue);
                    }
                }
                $groupOption                      = $this->convertGroupToProductOptionValues($groupOption);
                $productOptions[]                 = $groupOption;
                $this->productGroupNewOptionIds[] = $groupOption['group_option_id'];
            }
        }
        return $productOptions;
    }
}
