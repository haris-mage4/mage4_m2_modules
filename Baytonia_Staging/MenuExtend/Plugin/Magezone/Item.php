<?php

namespace Baytonia\MenuExtend\Plugin\Magezone;

class Item
{

	public function afterPrepareGeneralTab(\Magezon\NinjaMenus\Data\Element\Item $subject, $result)
	{

	   $container8 = $result->addContainerGroup(
                'container8',
                [
                    'sortOrder' => 200
                ]
            );
       
       $container8->addChildren(
                    'hide_on_web',
                    'toggle',
                    [
                        'sortOrder'       => 300,
                        'key'             => 'hide_on_web',
                        'templateOptions' => [
                            'label' => __('Hide on Web')
                        ]
                    ]
                );
                
                $container8->addChildren(
                    'hide_on_app',
                    'toggle',
                    [
                        'sortOrder'       => 300,
                        'key'             => 'hide_on_app',
                        'templateOptions' => [
                            'label' => __('Hide on App')
                        ]
                    ]
                );
        
        return $result;

	}

}