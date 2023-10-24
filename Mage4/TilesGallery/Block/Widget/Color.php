<?php

namespace Mage4\TilesGallery\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Color extends  Template implements BlockInterface
{
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $defaultColor = "#4087de";
        $value = $element->getValue() ?: $defaultColor;
        $element->setData('after_element_html', '
                <input type="text"
                    value="' . $value . '"
                    id="' . $element->getHtmlId() . '"
                    name="' . $element->getName() . '"
                      class="widget-option input-text admin__control-text admin_colorpicker"
                >
                <script type="text/javascript">
                require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
                    $currentElement' . $element->getHtmlId() . ' = $("#' . $element->getHtmlId() . '");
                    $currentElement' . $element->getHtmlId() . '.css("backgroundColor", "'. $value .'");
                    $currentElement' . $element->getHtmlId() . '.ColorPicker({
                        color: "' . $value . '",
                        onChange: function (hsb, hex, rgb) {
                            $currentElement' . $element->getHtmlId() . '.css("backgroundColor", "#" + hex).val("#" + hex);
                        }
                    });
                });
                </script><style>.colorpicker {z-index: 10010}</style>');
        $element->setValue(null);
        return $element;
    }
}
        // $defaultColor = "#000000";
        // $value = $element->getValue() ?: $defaultColor;
        // $element->setData('after_element_html', '
        //     <input type="text"
        //         value="' . $value . '"
        //         id="' . $element->getHtmlId() . '"
        //         name="' . $element->getName() . '"
        //         class="widget-option input-text admin__control-text admin_colorpicker"
        //     >
        //     <script type="text/javascript">
        //     require(["jquery", "jquery/colorpicker/js/colorpicker"], function ($) {
        //         $currentFont = $("#' . $element->getHtmlId() . '");
        //         $currentFont.css("backgroundColor", "'. $value .'");
        //         $currentFont.ColorPicker({
        //             color: "' . $value . '",
        //             onChange: function (hsb, hex, rgb) {
        //                 $currentFont.css("backgroundColor", "#" + hex).val("#" + hex);
        //             }
        //         });
        //     });
        //     </script><style>.colorpicker {z-index: 10010}</style>');
        // $element->setValue(null);
        // return $element;