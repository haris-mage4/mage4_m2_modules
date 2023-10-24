<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Customer\Api\CustomerRepositoryInterface;
use InformaticsCommerce\UseMyShippingAccount\Model\ResourceModel\Data\CollectionFactory;

class Option implements ResolverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $_optionsCollectionFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        CollectionFactory $optionsCollectionFactory
    ) {
        $this->logger = $logger;
        $this->_optionsCollectionFactory = $optionsCollectionFactory;
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
            $data = $this->getOptionFormData($args);
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
    private function getOptionFormData($getOptionFormData_array) : array
    {
        try {
            $option_form_data = [];
            $final_option_form_data = [];
            $option_form_items = $this->_optionsCollectionFactory->create();
            $option_form_items->addFieldToSelect('*');
            if ( isset($option_form_array['option_label']) && !empty($option_form_array['option_label']) ) {
                $option_form_items->addFieldToFilter('option_label', ['in' => $option_form_array['option_label']]);
            }
            if ( isset($option_form_array['option_code']) && !empty($option_form_array['option_code']) ) {
                $option_form_items->addFieldToFilter('option_code', ['in' => $option_form_array['option_code']]);
            }
            if ( isset($option_form_array['option_input_type']) && !empty($option_form_array['option_input_type']) ) {
                $option_form_items->addFieldToFilter('option_input_type', ['in' => $option_form_array['option_input_type']]);
            }
            if ( isset($option_form_array['option_values']) && !empty($option_form_array['option_values']) ) {
                $option_form_items->addFieldToFilter('option_values', ['in' => $option_form_array['option_values']]);
            }
            if ( isset($option_form_array['is_required']) && !empty($option_form_array['is_required']) ) {
                $option_form_items->addFieldToFilter('is_required', ['in' => $option_form_array['is_required']]);
            }
            if ( isset($option_form_array['apply_to']) && !empty($option_form_array['apply_to']) ) {
                $option_form_items->addFieldToFilter('apply_to', ['in' => $option_form_array['apply_to']]);
            }
            if ( isset($option_form_array['option_default_value']) && !empty($option_form_array['option_default_value']) ) {
                $option_form_items->addFieldToFilter('option_default_value', ['in' => $option_form_array['option_default_value']]);
            }
            if ( isset($option_form_array['sort_order']) && !empty($option_form_array['sort_order']) ) {
                $option_form_items->addFieldToFilter('sort_order', ['in' => $option_form_array['sort_order']]);
            }

            $option_form_items->setOrder('option_id','desc');
            foreach ($option_form_items as $item) {
                array_push($option_form_data, $item->getData());
            }
            foreach ($option_form_data as $key => $udata) {
                $final_option_form_data[$udata['option_id']]['option_id'] = $udata['option_id'];
                $final_option_form_data[$udata['option_id']]['option_label'] = $udata['option_label'];
                $final_option_form_data[$udata['option_id']]['option_code'] = $udata['option_code'];
                $final_option_form_data[$udata['option_id']]['option_input_type'] = $udata['option_input_type'];
                $final_option_form_data[$udata['option_id']]['option_values'] = $udata['option_values'];
                $final_option_form_data[$udata['option_id']]['is_required'] = $udata['is_required'];
                $final_option_form_data[$udata['option_id']]['apply_to'] = $udata['apply_to'];
                $final_option_form_data[$udata['option_id']]['option_default_value'] = $udata['option_default_value'];
                $final_option_form_data[$udata['option_id']]['sort_order'] = $udata['sort_order'];
            }
            return $final_option_form_data;
        } catch (NoSuchEntityException $e) {
            return [];
        } catch (LocalizedException $e) {
            throw new NoSuchEntityException(__($e->getMessage()));
        }
    }
}
