<?php
namespace Baytonia\Webpages\Controller\Index;
class Status extends \Magento\Framework\App\Action\Action
{

    protected $logger;
    protected $resultPageFactory;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $loggerInterface
        )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->logger               = $loggerInterface;
        parent::__construct($context);
    }

    public function execute()
    {   
        $response = $this->request->getParams();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/applePay.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($response, true)); 

        echo json_encode(array('response' => $response));
        exit;
    }
}
