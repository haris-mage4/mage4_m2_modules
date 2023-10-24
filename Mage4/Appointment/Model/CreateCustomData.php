<?php

namespace Mage4\Appointment\Model;

use Mage4\Appointment\Api\Data\DataInterface;
use Mage4\Appointment\Api\Data\DataInterfaceFactory;
use Mage4\Appointment\Api\DataRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateAppointmentData
{

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;
    /**
     * @var DataRepositoryInterface
     */
    protected $dataRepository;
    /**
     * @var DataInterfaceFactory
     */
    private $createAppointmentForm;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreRepositoryInterface $dataRepository
     * @param DataInterfaceFactory $storeInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        DataRepositoryInterface $dataRepository,
        DataInterfaceFactory $storeInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRepository = $dataRepository;
        $this->createAppointmentForm = $storeInterfaceFactory;
    }

    /**
     * @param array $data
     * @return DataInterface
     * @throws GraphQlInputException
     */
    public function execute(array $data): DataInterface
    {
        try {
            $this->vaildateData($data);
            $contactdata = $this->saveData($this->createContact($data));
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $contactdata;
    }

    /**
     * Guard function to handle bad request.
     * @param array $data
     * @throws LocalizedException
     */
    private function vaildateData(array $data)
    {
        if (!isset($data[DataInterface::NAME])) {
            throw new LocalizedException(__('First Name must be set'));
        }
        if (!isset($data[DataInterface::NAME])) {
            throw new LocalizedException(__('Last Name must be set'));
        }
        if (!isset($data[DataInterface::EMAIL])) {
            throw new LocalizedException(__('Email must be set'));
        }
        if (!isset($data[DataInterface::PHONE])) {
            throw new LocalizedException(__('Phone must be set'));
        }
        if (!isset($data[DataInterface::COMMENT])) {
            throw new LocalizedException(__('Comment must be set'));
        }
        if (!isset($data[DataInterface::ADDRESS])) {
            throw new LocalizedException(__('Address must be set'));
        }
    }

    /**
     * @param DataInterface $data
     * @return DataInterface
     * @throws CouldNotSaveException
     */
    private function saveData(DataInterface $data): DataInterface
    {
        $this->dataRepository->save($data);

        return $data;
    }

    /**
     * Create a model dto by given data array.
     *
     * @param array $data
     * @return DataInterface
     * @throws CouldNotSaveException
     */
    private function createContact(array $data): DataInterface
    {
        /** @var DataInterface $model */
        $model = $this->createAppointmentForm->create();
        $this->dataObjectHelper->populateWithArray(
            $model,
            $data,
            DataInterface::class
        );
        $model->setData($data);
        return $model;
    }
}
