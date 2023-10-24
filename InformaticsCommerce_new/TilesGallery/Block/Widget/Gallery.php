<?php

namespace InformaticsCommerce\TilesGallery\Block\Widget;

use Magento\Catalog\Helper\Category;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Widget\Helper\Conditions;

/**
 *
 */
class Gallery extends Template implements BlockInterface
{

    protected $_categoryFactory;
    protected $conditionsHelper;
    protected $categoryRepository;
    protected $_directory;
    protected $imageFactory;
    protected $storeManager;
    /**
     * @var string
     */
    protected $_template = 'widget/gallery.phtml';

    public function __construct(AdapterFactory $imageFactory, ImageFactory $helperImageFactory, Filesystem $filesystem, Category $category, CategoryRepository $categoryRepository,
                                Conditions     $conditionsHelper, CategoryFactory $categoryFactory, Template\Context $context, array $data = [])
    {
        $this->storeManager = $context->getStoreManager();
        $this->_categoryFactory = $categoryFactory;
        $this->conditionsHelper = $conditionsHelper;
        $this->categoryRepository = $categoryRepository;
        $this->imageFactory = $imageFactory;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        parent::__construct($context, $data);
    }

    public function getCategory()
    {
        $categoryIds = $this->getCategoryIds();

        if (!empty($categoryIds)) {
            $ids = explode(',', $categoryIds);
            $category = [];
            foreach ($ids as $id) {
                $name = $this->categoryRepository->get($id)->getName();
                $img = $this->getRealImage($this->categoryRepository->get($id)->getThumbnail());
                $url = $this->categoryRepository->get($id)->getUrl();
                $description = $this->categoryRepository->get($id)->getDescription();

                $category[] = [
                    'name' => $name,
                    'img' => $img,
                    'url' => $url,
                    'description' => $description,

                ];
            }
            return $category;
        }
        return '';

    }

    public function getCategoryIds()
    {
        $conditions = $this->getData('conditions')
            ? $this->getData('conditions')
            : $this->getData('conditions_encoded');

        if ($conditions) {
            $conditions = $this->conditionsHelper->decode($conditions);
        }

        foreach ($conditions as $key => $condition) {
            if (!empty($condition['attribute']) && $condition['attribute'] == 'category_ids') {
                return $condition['value'];
            }
        }
        return '';
    }

    public function getRealImage($imageName)
    {
        $realPath = $this->storeManager->getStore()->getBaseUrl() . $imageName;
        return $realPath;

    }

}
