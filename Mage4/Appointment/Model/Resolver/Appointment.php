<?php

namespace Mage4\Appointment\Model\Resolver;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Mage4\Appointment\Model\ResourceModel\Data\CollectionFactory;

class Appointment implements ResolverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    protected $_appointmentFormCollectionFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        GetCustomer $getCustomer,
        CustomerRepositoryInterface $customerRepository,
        CollectionFactory $appointmentFormCollectionFactory
    ) {
        $this->logger = $logger;
        $this->getCustomer = $getCustomer;
        $this->customerRepository = $customerRepository;
        $this->_appointmentFormCollectionFactory = $appointmentFormCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($args)) {
            throw new GraphQlAuthorizationException(
                __(
                    'No arguments specified',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        try {
            $data = $this->getAppointmentFormData($args);
            return $data;
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        } catch (LocalizedException $exception) {
            throw new GraphQlNoSuchEntityException(__($exception->getMessage()));
        }
    }

    /**
     *
     * @param int $context
     * @return array
     * @throws NoSuchEntityException|LocalizedException
     */
    private function getAppointmentFormData($getAppointmentFormData_array) : array
    {
        try {
            $appointment_form_data = [];
            $final_appointment_form_data = [];
            $appointment_form_items = $this->_appointmentFormCollectionFactory->create();
            $appointment_form_items->addFieldToSelect('*');
            if ( isset($appointment_form_array['firstname']) && !empty($appointment_form_array['firstname']) ) {
                $appointment_form_items->addFieldToFilter('firstname', ['in' => $appointment_form_array['firstname']]);
            }
            if ( isset($appointment_form_array['lastname']) && !empty($appointment_form_array['lastname']) ) {
                $appointment_form_items->addFieldToFilter('lastname', ['in' => $appointment_form_array['lastname']]);
            }
            if ( isset($appointment_form_array['email']) && !empty($appointment_form_array['email']) ) {
                $appointment_form_items->addFieldToFilter('email', ['in' => $appointment_form_array['email']]);
            }
            if ( isset($appointment_form_array['phone']) && !empty($appointment_form_array['phone']) ) {
                $appointment_form_items->addFieldToFilter('phone', ['in' => $appointment_form_array['phone']]);
            }
            if ( isset($appointment_form_array['address']) && !empty($appointment_form_array['address']) ) {
                $appointment_form_items->addFieldToFilter('address', ['in' => $appointment_form_array['address']]);
            }
            if ( isset($appointment_form_array['comment']) && !empty($appointment_form_array['comment']) ) {
                $appointment_form_items->addFieldToFilter('comment', ['in' => $appointment_form_array['comment']]);
            }
           
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
        } catch (NoSuchEntityException $e) {
            return [];
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }
}
