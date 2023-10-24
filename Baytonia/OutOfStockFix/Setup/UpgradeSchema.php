<?php
namespace Baytonia\OutOfStockFix\Setup;

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

        if (version_compare($context->getVersion(), '1.0.2', '<')) {

            //adding table tm_custom_shipping_methods
            if (!$installer->tableExists('guest_alert')) {
                $table = $installer->getConnection()->newTable($installer->getTable('guest_alert'))->
                    addColumn('alert_id', Table::TYPE_INTEGER, null, ['identity' => true, 'nullable' => false,
                    'primary' => true, 'unsigned' => true, ], 'Sync ID')->addColumn('product_id',
                    Table::TYPE_INTEGER, 11, ['nullable => false'], 'Product Id')->addColumn('email',
                    Table::TYPE_TEXT, 255, [], 'email')->addColumn('store_id', Table::TYPE_INTEGER,
                    11, ['nullable => false'], 'Store Id')->addColumn('website_id', Table::
                    TYPE_INTEGER, 11, ['nullable => false'], 'Website Id')->addColumn('updated_at', \Magento\Framework\DB\Ddl\Table::
                    TYPE_TIMESTAMP, null, ['nullable' => false], 'Created At')->addColumn('sync_status',
                    Table::TYPE_INTEGER, 11, [], 'Status of ync');
                $installer->getConnection()->createTable($table);

            }

        }
        if (version_compare($context->getVersion(), '1.0.4', '<')) {


            $tableName = $installer->getTable('guest_alert');
            $setup->getConnection()->addColumn($tableName, "product_name", ['type' => Table::
                TYPE_TEXT, 'lenght' => 255, 'nullable' => true, 'comment' => 'Product Name', ]);

            $setup->getConnection()->addColumn($tableName, "customer_name", ['type' => Table::
                TYPE_TEXT, 'lenght' => 255, 'nullable' => true, 'comment' => 'Customer Name', ]);

            $setup->getConnection()->addColumn($tableName, "type", ['type' => Table::
                TYPE_INTEGER, 'lenght' => 11, 'nullable' => true, 'comment' =>
                'Type Guest or customer', ]);


        }
        
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $setup->getConnection()
                ->changeColumn(
                    $installer->getTable('guest_alert'),
                    'type',
                    'type', [
                        'type' => Table::TYPE_INTEGER,
                        'lenght' => 11, 'nullable' => false, 'comment' => 'Type Guest or customer', 'default' => 0
                    ]
                );
        }
        
        if (version_compare($context->getVersion(), '1.0.6', '<')) {
            $tableName = $installer->getTable('guest_alert');
            $setup->getConnection()->addColumn($tableName, "customer_id", ['type' => Table::
                TYPE_INTEGER, 'lenght' => 11, 'nullable' => false, 'comment' => 'Customer ID', 'default' => 0 ]);
            $setup->getConnection()->addColumn($tableName, "foreign_id", ['type' => Table::
                TYPE_INTEGER, 'lenght' => 11, 'nullable' => false, 'comment' => 'Main Table ID', 'default' => 0 ]);
        }
        
        if (version_compare($context->getVersion(), '1.0.7', '<')) {
            $tableName = $installer->getTable('guest_alert');
            $setup->getConnection()->addColumn($tableName, "send_date", ['type' => Table::
                TYPE_DATETIME, 'nullable' => false, 'comment' => 'send alert datetime', 'default' => "0000-00-00 00:00:00" ]);
        }
        
        if (version_compare($context->getVersion(), '1.0.8', '<')) {
            $setup->getConnection()
                ->changeColumn(
                    $installer->getTable('guest_alert'),
                    'send_date',
                    'send_date', [
                        'type' => Table::TYPE_DATETIME,
                        'nullable' => true, 'comment' => 'send alert datetime',
                    ]
                );
        }


        $installer->endSetup();
    }

}
