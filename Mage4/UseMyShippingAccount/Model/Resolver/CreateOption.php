<?php

namespace InformaticsCommerce\UseMyShippingAccount\Model\Resolver;

use InformaticsCommerce\UseMyShippingAccount\Model\CreateOption as CreateOptionModel;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class CreateOption implements ResolverInterface
{
    /**
     * @var CreateOptionModel
     */
    private $createOption;

    /**
     * @param CreateOptionModel $CreateOption
     */
    public function __construct(CreateOptionModel $CreateOption)
    {
        $this->createOption = $CreateOption;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['input']) || !is_array($args['input'])) {
            throw new GraphQlInputException(__('"input" value should be specified'));
        }

        return ['shippingoptions_form_data' => $this->createOption->execute($args['input'])];
    }
}
