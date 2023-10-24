<?php

namespace Baytonia\AmastyVisualMerchExtended\Plugin\Catalog\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ObjectManager; 

class Category
{
    /**
     * @var \Amasty\VisualMerch\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Amasty\VisualMerch\Model\Product\AdminHtmlDataProvider
     */
    private $adminhtmlDataProvider;

    /**
     * @var \Amasty\VisualMerch\Model\ResourceModel\Product
     */
    private $productPositionDataResource;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\VisualMerch\Model\RuleFactory $ruleFactory,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\VisualMerch\Model\Product\AdminhtmlDataProvider $adminhtmlDataProvider,
        \Amasty\VisualMerch\Model\ResourceModel\Product $productPositionDataResource
    ) {
        $this->storeManager = $storeManager;
        $this->ruleFactory = $ruleFactory;
        $this->request = $request;
        $this->adminhtmlDataProvider = $adminhtmlDataProvider;
        $this->productPositionDataResource = $productPositionDataResource;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Closure $proceed
     * @param \Magento\Catalog\Model\Category $category
     * @param integer $entityId
     * @param array|null $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Category
     */
    public function aroundLoad(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Closure $proceed,
        \Magento\Catalog\Model\Category $category,
        $entityId,
        $attributes = []
    ) {
        $result = $proceed($category, $entityId, $attributes);

        if (empty($attributes) || in_array('amasty_dynamic_conditions', $attributes)) {
            $rule = $this->ruleFactory->create();
            $conditions = $category->getData('amasty_dynamic_conditions');
            $category->setData('amasty_rule', $rule->setConditionsSerialized($conditions));
        }

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     * @throws LocalizedException
     */
    public function beforeSave(
        \Magento\Catalog\Model\ResourceModel\Category $subject,
        \Magento\Catalog\Model\Category $category
    ) {
        $this->category = $category;
        $this->adminhtmlDataProvider->setCategoryId((int)$category->getId());
         
        $connection = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection')->getConnection();

        $cisess_data['position'] = 0;
        $connection->update(
            $connection->getTableName('catalog_category_product'),
            $cisess_data,
            ['category_id = ?' => (int)$category->getId()]
        );


        if ($this->request->getControllerName() == 'category') {
            if ($category
                && $category->getData('amlanding_is_dynamic')
                && $this->isMassAction()
            ) {
                throw new LocalizedException(
                    __(
                        'Category #%1 is dynamic. Please go to Category Edit Page for making any changes.',
                        $category->getId()
                    )
                );
            }

            if (!$this->isMassAction()) {
                $rule = $this->request->getParam('rule');
                if (is_array($rule) && isset($rule['conditions'])) {
                    $conditions = $rule['conditions'];
                    $conditionsSerialised = $this->ruleFactory->create()
                        ->loadPost(['conditions' => $conditions])
                        ->beforeSave()
                        ->getConditionsSerialized();
                    $category->setData('amasty_dynamic_conditions', $conditionsSerialised);
                }
                $category->setProductPositionData($this->adminhtmlDataProvider->getProductPositionData());
                $category->setData('amasty_category_product_sort', $this->adminhtmlDataProvider->getSortOrder());
                $this->assignCategoryProducts($category);
                $category->unsetData('amlanding_page_id');
            }
        }

        return [$category];
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Category $subject
     * @param $result
     * @return mixed
     */
    public function afterSave(\Magento\Catalog\Model\ResourceModel\Category $subject, $result)
    {
        $this->productPositionDataResource->saveProductPositionData($this->category);
        return $result;
    }

    /**
     * @return bool
     */
    private function isMassAction()
    {
        return $this->request->getControllerName() == 'massaction';
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return $this
     */
    private function assignCategoryProducts(\Magento\Catalog\Model\Category $category)
    {
        $productIds = [];
        $stores = $this->storeManager->getStores();
        $parentIds = $category->getParentIds();
        foreach ($stores as $store) {
            if (in_array($store->getRootCategoryId(), $parentIds)
                || $store->getRootCategoryId() == $category->getId()
            ) {
                $productPositionData = $this->adminhtmlDataProvider->getFullPositionDataByStoreId($store->getId());
                $productIds = array_replace($productPositionData, $productIds);
            }
        }

        $category->setPostedProducts($productIds);

        return $this;
    }
}
