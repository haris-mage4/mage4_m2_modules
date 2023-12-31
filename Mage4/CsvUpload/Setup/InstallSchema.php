<?php

namespace Mage4\CsvUpload\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Mage4 CSV setup InstallSchema class
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $table_Mage4_csvupload_csv = $setup->getConnection()->newTable($setup->getTable('Mage4_csvupload_csv'));

        $table_Mage4_csvupload_csv->addColumn(
            'csv_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true,'nullable' => false,'primary' => true,'unsigned' => true],
            'Entity ID'
        );

        $table_Mage4_csvupload_csv->addColumn(
            'filename',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            255,
            [],
            'Filename'
        );

        $table_Mage4_csvupload_csv->addColumn(
            'uploaded_at',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
            null,
            [],
            'Uploaded At'
        );

        $table_Mage4_csvupload_csv->addColumn(
            'processed',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            [],
            'processed'
        );


        $setup->getConnection()->createTable($table_Mage4_csvupload_csv);
    }
}
