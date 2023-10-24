<?php

namespace Mage4\Appointment\Controller\Form;

use Mage4\Appointment\Api\DataRepositoryInterface;
use Mage4\Appointment\Api\Data\DataInterface;
use Mage4\Appointment\Api\Data\DataInterfaceFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var DataRepositoryInterface
     */
    protected $dataRepository;

    /**
     * @var DataInterfaceFactory
     */
    protected $dataFactory;

    public function __construct(
        Context                 $context,
        DataRepositoryInterface $dataRepository,
        Manager                 $messageManager,
        DataInterfaceFactory    $dataFactory
    )
    {
        $this->messageManager = $messageManager;
        $this->dataFactory = $dataFactory;
        $this->dataRepository = $dataRepository;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = (array)$this->getRequest()->getPost();
        if (!empty($post)) {
            try {
                $model = $this->dataFactory->create();
                $model->setData($post);
                //$this->sendEmail();

                $this->dataRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You request has been saved. We will contact you shortly.'));
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/appointment/form/index');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            return $resultRedirect;
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    public function sendEmail()
    {
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // /** @var \Magento\Framework\Translate\Inline\StateInterface $stateInterface */
        // $stateInterface = $objectManager->get('\Magento\Framework\Translate\Inline\StateInterface');
        // /** @var \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder */
        // $transportBuilder = $objectManager->get('\Magento\Framework\Mail\Template\TransportBuilder');
        // /** @var \Magento\Store\Model\StoreManagerInterface $storeManagerInterface */
        // $storeManagerInterface = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        // /** @var \Magento\Contact\Model\ConfigInterface $configInterface */
        // $configInterface = $objectManager->get('\Magento\Contact\Model\ConfigInterface');

        // this is an example and you can change template id,fromEmail,toEmail,etc as per your need.

        // $fromEmail = 'Ahmed@mage4.com';  // sender Email id
        // $fromName = 'Ahmed';             // sender Name
        // $toEmail = ['ahmed@mage4.com', 'mage12556@gmail.com']; // receiver email id

        try {
            // template variables pass here
            $templateVars = [
                // 'name' => 'Kashif',
                // 'email' => 'test@test.com',
                // 'telephone' => '111222333',
                // 'comment' => 'test 123'
            ];

            $variables['data'] = new DataObject($templateVars);

            $storeId = $storeManagerInterface->getStore()->getId();

            $from = ['email' => $fromEmail, 'name' => $fromName];
            $stateInterface->suspend();

            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $templateOptions = [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ];

            $transport = $transportBuilder->setTemplateIdentifier($configInterface->emailTemplate())
                ->setTemplateOptions($templateOptions)
                ->setTemplateVars($variables)
                ->setFrom($from)
                ->addTo($toEmail)
                ->getTransport();

            $transport->sendMessage();
            $stateInterface->resume();
            echo "success";
        } catch (\Exception $e) {
            /** @var \Psr\Log\LoggerInterface $loggerInterface */
            $loggerInterface = $objectManager->get('\Psr\Log\LoggerInterface');
            $loggerInterface->critical($e->getMessage());
            echo $e->getMessage();
            exit;
        }
    }
}
