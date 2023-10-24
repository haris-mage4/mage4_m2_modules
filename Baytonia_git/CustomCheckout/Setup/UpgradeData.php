<?php


namespace Baytonia\CustomCheckout\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Cms\Api\BlockRepositoryInterface
     */
    protected $blockRepository;
    
    /**
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;
    
    /**
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;
    
    /**
     *
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;
    
    /**
     * 
     * @param \Magento\Cms\Api\BlockRepositoryInterface $blockRepository
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\File\Csv $csvProcessor
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Cms\Api\BlockRepositoryInterface $blockRepository,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\File\Csv $csvProcessor,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->blockRepository = $blockRepository;
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    
    /**
     * Upgrades data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $cities = $this->getCities();
            if ($cities) {
                $cities = array_slice($cities, 1);
                $cities  = array_map(function ($i) {
                    return $i[2];
                }, $cities);
                $cities = array_unique($cities);
                foreach ($cities as $city) {
                    
                    $bind = ['country_id' => 'SA', 'code' => $city, 'default_name' => $city];
                    $setup->getConnection()->insert(
                        $setup->getTable('directory_country_region'),
                        $bind
                    );
                    $regionId = $setup->getConnection()->lastInsertId(
                        $setup->getTable('directory_country_region')
                        );
                    
                    $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $city];
                    $setup->getConnection()->insert(
                        $setup->getTable('directory_country_region_name'),
                        $bind
                    );
                    
                    $bind = ['locale' => 'ar_SA', 'region_id' => $regionId, 'name' => $city];
                    $setup->getConnection()->insert(
                        $setup->getTable('directory_country_region_name'),
                        $bind
                    );
                }
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            echo 'Upgrade to 1.0.2';
            $this->setConfig($setup, 'general/region/display_all', 1, 'default', 0);
            $this->setConfig($setup, 'general/region/state_required', 'AF,BH,KW,OM,SA,AE', 'default', 0);
            $eavSetup->updateAttribute('customer_address','city','is_required','false');
            $eavSetup->updateAttribute('customer_address','region_id','is_required','false');
            $eavSetup->updateAttribute('customer_address','region_id','is_visible','false');
            $eavSetup->updateAttribute('customer_address','region','is_visible','false');
        }
        
        $setup->endSetup();
    }
    
    /**
     * 
     * @return boolean|array|array[]
     */
    public function getCities()
    {
        $filePath = $this->filesystem->getDirectoryRead(DirectoryList::PUB)
            ->getAbsolutePath().'data/import/export.csv';
        if (!file_exists($filePath)) {
            return false;
        }
        
        return $this->csvProcessor->getData($filePath);
    }
    
    private function setConfig($setup, $path, $value, $scope, $scope_id)
    {
        $configTable = $setup->getTable('core_config_data');
        $where = 'path = "' . $path . '" AND scope="' . $scope . '"';
        $select = $setup->getConnection()->select()->from($configTable)->where($where);
        if (empty($setup->getConnection()->fetchRow($select))) {
            $setup->getConnection()->insert(
                $configTable,
                [
                    'value'     => $value,
                    'scope'     => $scope,
                    'scope_id'  => $scope_id,
                    'path'      => $path
                ]
                );
        } else {
            $setup->getConnection()->update(
                $configTable,
                ['value' => $value],
                'path="' . $path . '" AND scope="' . $scope . '"'
                );
        }
    }
}