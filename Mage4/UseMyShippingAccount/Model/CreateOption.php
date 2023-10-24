<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model;

use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterface;
use InformaticsCommerce\UseMyShippingAccount\Api\Data\DataInterfaceFactory;
use InformaticsCommerce\UseMyShippingAccount\Api\DataRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class CreateOption
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
    private $createOptionForm;

    /**
     * @param DataObjectHelper $dataObjectHelper
     * @param StoreRepositoryInterface $dataRepository
     * @param DataInterfaceFactory $dataInterfaceFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        DataRepositoryInterface $dataRepository,
        DataInterfaceFactory $dataInterfaceFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataRepository = $dataRepository;
        $this->createOptionForm = $dataInterfaceFactory;
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
            $optiondata = $this->saveData($this->createOption($data));
        } catch (\Exception $e) {
            throw new GraphQlInputException(__($e->getMessage()));
        }

        return $optiondata;
    }

    /**
     * Guard function to handle bad request.
     * @param array $data
     * @throws LocalizedException
     */
    private function vaildateData(array $data)
    {
        if (!isset($data[DataInterface::OPTION_LABEL])) {
            throw new LocalizedException(__('This Field is required'));
        }
        if (!isset($data[DataInterface::OPTION_LABEL])) {
            throw new LocalizedException(__('This Field is required'));
        }
        if (!isset($data[DataInterface::OPTION_INPUT_TYPE])) {
            throw new LocalizedException(__('This Field is required'));
        }
        if (!isset($data[DataInterface::IS_REQUIRED])) {
            throw new LocalizedException(__('This Field is required'));
        }
        if (!isset($data[DataInterface::APPLY_TO])) {
            throw new LocalizedException(__('This Field is required'));
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
    private function createOption(array $data): DataInterface
    {
        /** @var DataInterface $model */
        $model = $this->createOptionForm->create();
        $this->dataObjectHelper->populateWithArray(
            $model,
            $data,
            DataInterface::class
        );
        $model->setData($data);
        return $model;
    }
}
