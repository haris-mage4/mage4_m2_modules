<?php

namespace InformaticsCommerce\ProductAskQuestions\Model;


use InformaticsCommerce\ProductAskQuestions\Api\Product\QuestionInterface;
use Magento\Framework\Model\AbstractModel;

class Question extends AbstractModel implements QuestionInterface
{
    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function getProductSku()
    {
        return $this->getData(self::PRODUCT_SKU);
    }

    public function getCustomername()
    {
        return $this->getData(self::CUSTOMER_NAME);
    }

    public function getEmail()
    {
        return $this->getData(self::EMAIL);
    }

    public function getPhonenumber()
    {
        return $this->getData(self::PHONE_NUMBER);
    }

    public function getQuestion()
    {
        return $this->getData(self::QUESTION);

    }

    public function setProductSku($sku)
    {
        $this->setData(self::PRODUCT_SKU, $sku);
    }

    public function setCustomername($customername)
    {
        $this->setData(self::CUSTOMER_NAME, $customername);
    }

    public function setEmail($email)
    {
        $this->setData(self::EMAIL, $email);
    }

    public function setPhonenumber($phonenumber)
    {
        $this->setData(self::PHONE_NUMBER, $phonenumber);
    }

    public function setQuestion($question)
    {
        $this->setData(self::QUESTION, $question);
    }


    protected function _construct()
    {
        $this->_init('InformaticsCommerce\ProductAskQuestions\Model\ResourceModel\Question');
    }


}
