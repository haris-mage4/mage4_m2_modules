<?php

namespace InformaticsCommerce\UseMyShippingAccount\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;


class Select extends AbstractFieldArray
{
    /**
     * @var Options
     */
    private $optionRenderer;

    protected function _prepareToRender()
    {
        $this->addColumn('option_value', [
            'label' => __('Option Value'),
            'renderer' => $this->getTaxRenderer()
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Value');
    }

    protected function _prepareArrayRow(DataObject $row)
    {
        $options = [];

        $tax = $row->getTax();
        if ($tax !== null) {
            $options['option_' . $this->getTaxRenderer()->calcOptionHash($tax)] = 'selected="selected"';
        }

        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @return Options
     * @throws LocalizedException
     */
    private function getTaxRenderer()
    {
        if (!$this->optionRenderer) {
            $this->optionRenderer = $this->getLayout()->createBlock(
                Options::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->optionRenderer;
    }
}
