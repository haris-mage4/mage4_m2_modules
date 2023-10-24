<?php

namespace Mage4\AdvanceTopNavbar\Block\Html;

use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection;
use Magento\Framework\Data\Tree\NodeFactory;
use Magento\Framework\Data\TreeFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Theme\Block\Html\Topmenu;


class OverrideTopmenu extends Topmenu
{
    protected $_categoryRespositry;
    private $_directoryList;

    public function __construct(CategoryRepository $categoryRepository, Template\Context $context, NodeFactory $nodeFactory, TreeFactory $treeFactory, array $data = [])
    {
        $this->_categoryRespositry = $categoryRepository;
        parent::__construct($context, $nodeFactory, $treeFactory, $data);
    }

    protected function _getHtml(Node $menuTree, $childrenWrapClass, $limit, array $colBrakes = [])
    {
        $html = '';

        $children = $menuTree->getChildren();
        $childLevel = $this->getChildLevel($menuTree->getLevel());
        $this->removeChildrenWithoutActiveParent($children, $childLevel);

        $counter = 1;
        $childrenCount = $children->count();

        $parentPositionClass = $menuTree->getPositionClass();
        $itemPositionClassPrefix = $parentPositionClass ? $parentPositionClass . '-' : 'nav-';

        /** @var Node $child */
        foreach ($children as $child) {
            $child->setLevel($childLevel);
            $child->setIsFirst($counter === 1);
            $child->setIsLast($counter === $childrenCount);
            $child->setPositionClass($itemPositionClassPrefix . $counter);

            $outermostClassCode = '';
            $outermostClass = $menuTree->getOutermostClass();

            if ($childLevel === 0 && $outermostClass) {
                $outermostClassCode = ' class="' . $outermostClass . '" ';
                $this->setCurrentClass($child, $outermostClass);
            }

            if ($this->shouldAddNewColumn($colBrakes, $counter)) {
                $html .= '</ul></li><li class="column"><ul>';
            }
              if ($childLevel === 0 && str_contains($child->getId(), 'category-node-')){
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                        $child->getName()
                    ) . '</span></a>' .
                    '<ul style="display: none;" class="products-section">' .
                      $this->getLayout()
                          ->getBlock('list_product_nav')
                          ->setData('product_collection', $this->getCategoryProducts($child))
                          ->setData('category', $child)
                          ->toHtml()
                    .'</ul>'
                    .'</li>';
            }else{
                $html .= '<li ' . $this->_getRenderedMenuItemAttributes($child) . '>';
                $html .= '<a href="' . $child->getUrl() . '" ' . $outermostClassCode . '><span>' . $this->escapeHtml(
                        $child->getName()
                    ) . '</span></a>' . $this->_addSubMenu(
                        $child,
                        $childLevel,
                        $childrenWrapClass,
                        $limit
                    ) . '</li>';
            }

            $counter++;
        }

        if (is_array($colBrakes) && !empty($colBrakes) && $limit) {
            $html = '<li class="column"><ul>' . $html . '</ul></li>';
        }

        return $html;
    }

    private function getCategoryProducts($category){
        $catId = explode('category-node-',$category->getId())[1];
        $productCollection = $this->_categoryRespositry->get($catId)
          ->getProductCollection()
          // ->setOrder('position','DESC')
          ->setOrder('name','ASC')
          ->setPageSize(4)
          ->addAttributeToSelect('*');
        return $productCollection;
    }

    private function removeChildrenWithoutActiveParent(Collection $children, int $childLevel): void
    {
        /** @var Node $child */
        foreach ($children as $child) {
            if ($childLevel === 0 && $child->getData('is_parent_active') === false) {
                $children->delete($child);
            }
        }
    }
    /**
     * Retrieve child level based on parent level
     *
     * @param int $parentLevel
     *
     * @return int
     */
    private function getChildLevel($parentLevel): int
    {
        return $parentLevel === null ? 0 : $parentLevel + 1;
    }
    /**
     * Check if new column should be added.
     *
     * @param array $colBrakes
     * @param int $counter
     * @return bool
     */
    private function shouldAddNewColumn(array $colBrakes, int $counter): bool
    {
        return count($colBrakes) && $colBrakes[$counter]['colbrake'];
    }
    private function getProductMediaPath()
    {
        return $this->_storeManager->getStore()->getBaseUrl(
                UrlInterface::URL_TYPE_MEDIA
            ) . 'catalog/product';
    }

}
