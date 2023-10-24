<?php
namespace Baytonia\CustomApis\Plugin\Repository;


class RewardsRepository
{
    
    public function __construct(\Magento\Framework\App\State $state)
        {
           $this->state = $state;
        }
    
    public function afterGetCustomerRewardBalance(\Amasty\Rewards\Model\Repository\RewardsRepository
        $subject, $result, $customerId)
    {
        if("webapi_rest" == $this->state->getAreaCode()){
            $responseArray = array();
        $responseArray["points"] = $result;
        $responseArray["customer_id"] = $customerId;
        return array($responseArray);
        }else{
            return $result;
        }
        
    }
}
