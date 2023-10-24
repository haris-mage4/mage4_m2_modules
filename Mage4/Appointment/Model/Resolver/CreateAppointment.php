<?php

namespace Mage4\Appointment\Model\Resolver;

use Mage4\Appointment\Model\CreateAppointmentData as CreateAppointmentData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateAppointment implements ResolverInterface
{
    /**
     * @var CreateAppointmentData
     */
    private $CreateAppointmentData;

    /**
     * @param CreatPickUpStore $CreateAppointmentData
     */
    public function __construct(CreateAppointmentData $CreateAppointmentData)
    {
        $this->CreateAppointmentData = $CreateAppointmentData;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        return ['appointment_form_data' => $this->CreateAppointmentData->execute($args['input'])];
    }
}
