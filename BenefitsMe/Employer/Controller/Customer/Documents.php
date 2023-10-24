<?php
namespace BenefitsMe\Employer\Controller\Customer;

class Documents extends \Magento\Framework\App\Action\Action {
	public function execute() {
		$this->_view->loadLayout();
		$this->_view->renderLayout();
	}
}