<?php

namespace InformaticsCommerce\TaxExempt\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Config;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class TaxExemptNumber implements DataPatchInterface
{

    protected $_eavConfig;
    /**
     * ModuleDataSetupInterface
     *
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;
    /**
     * EavSetupFactory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory          $eavSetupFactory,
        Config                   $eavConfig
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->_eavConfig = $eavConfig;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $attributes = ['tax_exempt_number', 'tax_exempt_file'];
        $this->moduleDataSetup->startSetup();
        foreach ($attributes as $attribute) {
            if ($attribute === 'tax_exempt_number') {
                $eavSetup->addAttribute(Customer::ENTITY, $attribute, [
                    'type' => 'varchar',
                    'label' => 'Tax Exempt Number',
                    'input' => 'text',
                    'required' => false,
                    'visible' => true,
                    'source' => '',
                    'backend' => '',
                    'user_defined' => true,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                    'position' => 10,
                    'system' => false,
                ]);
            } elseif ($attribute === 'tax_exempt_file') {
                $eavSetup->addAttribute(Customer::ENTITY, $attribute, [
                    'type' => 'varchar',
                    'label' => 'Tax Exempt File',
                    'input' => 'hidden',
                    'required' => false,
                    'visible' => true,
                    'source' => '',
                    'backend' => '',
                    'user_defined' => false,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => true,
                    'is_filterable_in_grid' => true,
                    'is_searchable_in_grid' => true,
                    'position' => 20,
                    'system' => false,
                ]);
            }
            $eavSetup->addAttributeToSet(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                null,
                $attribute);

            $taxAttr = $this->_eavConfig->getAttribute(Customer::ENTITY, $attribute);
            $taxAttr->setData('used_in_forms', [
                'adminhtml_customer',
            ]);
            $taxAttr->save();

        }

        $this->moduleDataSetup->endSetup();

    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
