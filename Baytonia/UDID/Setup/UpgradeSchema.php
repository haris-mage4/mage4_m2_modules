<?php
namespace Baytonia\UDID\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {


        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {

            //adding table tm_custom_shipping_methods
            if (!$installer->tableExists('app_udid')) {
                $table = $installer->getConnection()->newTable($installer->getTable('app_udid'))->
                    addColumn('udid_id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false,
                    'primary' => true, 'unsigned' => true, ], 'UDID primary ID')->addColumn('udid_count',
                    Table::TYPE_INTEGER, 11, ['nullable => false'], 'udid count Id')->addColumn('udid',
                    Table::TYPE_TEXT, 255, [], 'udid')->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::
                    TYPE_TIMESTAMP, null, ['nullable' => false], 'Created At');
                $installer->getConnection()->createTable($table);

            }

            if (version_compare($context->getVersion(), '1.0.2', '<')) {
               
            }

        }


        $installer->endSetup();
    }

}
