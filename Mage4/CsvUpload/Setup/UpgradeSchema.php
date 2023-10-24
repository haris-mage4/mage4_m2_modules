<?php

namespace Mage4\CsvUpload\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema  implements UpgradeSchemaInterface
{
    public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
        $installer = $setup;

        $installer->startSetup();
        if(version_compare($context->getVersion(), '1.0.3', '<')) {
            $installer->getConnection()->addColumn(
                $installer->getTable( 'Mage4_csvupload_csv' ),
                'file_path',
                [
                   'type' => Table::TYPE_TEXT,
                  'length' => 255,
                  'nullable' => true,
                  'default' => '',
                  'comment' => 'File Path'
                ]
            );
        }



        $installer->endSetup();
    }
}
