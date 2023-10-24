<?php
namespace Baytonia\CustomApis\Model;

class MenuServices
{


    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface, \Magento\Store\Model\StoreManagerInterface
        $storeManager, \Magento\Store\Model\StoreRepository $storeRepository, \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
        $categoryCollectionFactory, \Magento\Catalog\Model\ResourceModel\Category $categoryResourceModel, \Magento\Catalog\Model\CategoryRepository
        $categoryRepository, \Magento\Catalog\Model\Category $categoryModel, \Magento\Framework\App\ResourceConnection
        $resourceConnection, \Baytonia\CustomApis\Helper\Cache $cacheHelper, \Magezon\NinjaMenus\Model\ResourceModel\Menu\Collection
        $menucollection)
    {
        $this->_scopeConfig = $scopeInterface;
        $this->storeManager = $storeManager;
        $this->storeRepository = $storeRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->categoryResourceModel = $categoryResourceModel;
        $this->categoryRepository = $categoryRepository;
        $this->categoryModel = $categoryModel;
        $this->resourceConnection = $resourceConnection;
        $this->_cacheHelper = $cacheHelper;
        $this->_menucollection = $menucollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getMenuData()
    {
        $storeId = 1;
        try {
            $menus = $this->_menucollection->addFieldToFilter("identifier", "top-menu")->
                getFirstItem();

            $cacheId = $this->_cacheHelper->getId("menu", $storeId);
            if ($cache = $this->_cacheHelper->load($cacheId)) {
                echo $cache;
                die();
            }


            $res = $menus['profile'];
            $json = json_decode($res, true);

            foreach ($json['elements'] as $key => $value) {
                
                $hideonApp = (isset($value["hide_on_app"]))?$value["hide_on_app"]:0;
                if($hideonApp){
                    continue;
                }
                
                if ($value["item_type"] == "custom") {

                    $childCategory['name'] = @$value['title'] ? @$value['title'] : "";
                    $childCategory['label'] = @$value['label'] ? @$value['label'] : "";
                    $childCategory['id'] = $key + 10;
                    $childCategory['url'] = @$value['custom_link'] ? @$value['custom_link'] : "";
                    if (!$childCategory['label'] == "") {
                        $childCategory['color'] = @$value['label_background_color'] ? @$value['label_background_color'] :
                            "#000000";
                    } else {
                        $childCategory['color'] = "";
                    }
                    $childCategory["child"] = array();

                } else {
                    if (empty($value['elements'])) {
                        if (!empty($value['category_id'])) {
                            $category = $this->categoryModel->load(@$value['category_id']);
                            $childCategory['name'] = @$value['title'] ? @$value['title'] : $category->
                                getName();
                            $childCategory['label'] = @$value['label'] ? @$value['label'] : "";
                            $childCategory['id'] = @$category->getId() ? $category->getId() : "";
                            $childCategory['url'] = @$category->getUrl() ? $category->getUrl() : "";
                            if (!$childCategory['label'] == "") {
                                $childCategory['color'] = @$value['label_background_color'] ? @$value['label_background_color'] :
                                    "#000000";
                            } else {
                                $childCategory['color'] = "";
                            }
                            $childCategory["child"] = $this->getCategory($category);

                        }
                    } else {
                        $category = $this->categoryModel->load(@$value['category_id']);
                        $childCategory['name'] = @$value['title'] ? @$value['title'] : $category->
                            getName();
                        $childCategory['label'] = @$value['label'] ? @$value['label'] : "";
                        $childCategory['id'] = @$category->getId() ? $category->getId() : "";
                        $childCategory['url'] = @$category->getUrl() ? $category->getUrl() : "";
                        if (!$childCategory['label'] == "") {
                            $childCategory['color'] = @$value['label_background_color'] ? @$value['label_background_color'] :
                                "#000000";
                        } else {
                            $childCategory['color'] = "";
                        }
                        $childCategory["child"] = $this->getCategoryStatic(@$value['elements']);
                    }
                }

                $child[] = $childCategory;
            }
            $ress["message"] = 'Success';
            $ress["data"] = $child;

            $this->_cacheHelper->save(json_encode($ress, JSON_UNESCAPED_UNICODE), $cacheId);
            echo json_encode($ress, JSON_UNESCAPED_UNICODE);
            die();

        }
        catch (\Exception $e) {
            $ress["message"] = __($e->getMessage());
            $dataTosend = json_encode($ress, JSON_UNESCAPED_UNICODE);
            echo $dataTosend;
            die();
        }


    }

    public function getCategory($category)
    {
        $i = 0;
        $ChildCategoryValue = [];
        try {
            $parentcategories = $category;
            $categories = $parentcategories->getChildrenCategories();
            foreach ($categories as $category) {

                $ChildCategoryValue[$i] = ['name' => $category->getName(), 'id' => $category->
                    getId(), 'url' => $category->getUrl()];
                $childCat = $this->getCategory($category);
                if ($childCat) {
                    $ChildCategoryValue[$i]['child'] = $childCat;
                }
                $i++;
            }
        }
        catch (\Throwable $th) {
            //throw $th;
        }

        return $ChildCategoryValue;
    }

    public function getCategoryStatic($variable)
    {

        $i = 0;
        $ChildCategoryValue1 = [];
        foreach ($variable as $key => $value) {
            if ($key < 1)
                continue;
            $itemType = @$value['item_type'];
            $value['label'] = @$value['label'] ? @$value['label'] : "";
            $category = $this->categoryModel->load(@$value['category_id']);
            $ChildCategoryValue1[$i] = ['name' => @$value['title'], 'label' => $value['label'],
                'id' => @$value['category_id'], 'url' => @$category->getUrl()];
            if ($itemType == 'custom') {
                $value['id'] = $value['id'] ? $value['id'] : "";
                $ChildCategoryValue1[$i] = ['name' => $value['title'], 'label' => $value['label'],
                    'id' => $value['id'], 'url' => $value['custom_link']];
            }
            $i++;
        }
        return $ChildCategoryValue1;
    }
}
