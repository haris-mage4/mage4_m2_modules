<?php
namespace Baytonia\ProductAltUpdate\Console\Command;

use Magento\Catalog\Api\ProductManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Registry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductAltUpdate extends Command
{

    protected $productModel;
    protected $productRepository;
    protected $productManagement;
    protected $searchCriteriaBuilder;
    protected $registry;
    protected $state;
    
    public function __construct(
        \Magento\Catalog\Model\Product $productModel,
        ProductRepositoryInterface $productRepositoryInterface,
        Registry $registry,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        State $state
    ) {
        $this->productModel = $productModel;
        $this->productRepository = $productRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->registry = $registry;
        $this->state = $state;
        parent::__construct();
    }
    
    protected function configure()
    {
        $this->setName('baytonia:productaltupdate')->setDescription('Updates Image Alt.');
        $this->addArgument('product_id', InputArgument::OPTIONAL, 'Start from Product ID');
        parent::configure();
    }
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->registry->register('isSecureArea', true, true);
        try {
            $this->state->getAreaCode();
        }
        catch (\Exception $e) {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        }

        $output->writeln('Updating Image Tags');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection=$objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $_products = $productCollection->addAttributeToSelect('*')->load();

        foreach ($_products as $products) {
            $output->writeln('Updating: ' . $products->getName().'---'.$products->getSku());
            $title = $products->getName();
            $product = $this->productModel->load($products->getId());
            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
            if (count($existingMediaGalleryEntries) > 0) {
                foreach ($existingMediaGalleryEntries as $key => $entry) {
                    $entry->setLabel($title);
                }
                try {
                    $product->setMediaGalleryEntries($existingMediaGalleryEntries)->setStoreId(0)->save();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }  
    }
}

