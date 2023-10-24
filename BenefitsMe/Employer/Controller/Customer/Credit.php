<?php
namespace BenefitsMe\Employer\Controller\Customer;

class Credit extends \Magento\Framework\App\Action\Action {
	public function execute() {
		$this->_view->loadLayout();
		$this->_view->renderLayout();
	}
}