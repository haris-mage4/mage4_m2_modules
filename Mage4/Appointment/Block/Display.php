<?php

namespace Mage4\Appointment\Block;

use Mage4\Appointment\Model\ResourceModel\Data\CollectionFactory;
use Magento\Framework\View\Element\Template\Context;

class Display extends \Magento\Framework\View\Element\Template
{
	protected $_appointmentFormCollectionFactory;

	public function __construct(
		Context $context,
		CollectionFactory $appointmentFormCollectionFactory
	) {
		$this->_appointmentFormCollectionFactory = $appointmentFormCollectionFactory;
		parent::__construct($context);
	}

	public function getContactCollection(){
		$appointment_form_data = [];
        $final_appointment_form_data = [];
        $appointment_form_items = $this->_appointmentFormCollectionFactory->create();
        $appointment_form_items->setOrder('id','desc');
        foreach ($appointment_form_items as $item) {
            array_push($appointment_form_data, $item->getData());
        }
        foreach ($appointment_form_data as $key => $udata) {
            $final_appointment_form_data[$udata['id']]['id'] = $udata['id'];
            $final_appointment_form_data[$udata['id']]['firstname'] = $udata['firstname'];
            $final_appointment_form_data[$udata['id']]['lastname'] = $udata['lastname'];
            $final_appointment_form_data[$udata['id']]['email'] = $udata['email'];
            $final_appointment_form_data[$udata['id']]['phone'] = $udata['phone'];
            $final_appointment_form_data[$udata['id']]['address'] = $udata['address'];
            $final_appointment_form_data[$udata['id']]['comment'] = $udata['comment'];
            $final_appointment_form_data[$udata['id']]['created_at'] = $udata['created_at'];
        }
        return $final_appointment_form_data;
	}
}
