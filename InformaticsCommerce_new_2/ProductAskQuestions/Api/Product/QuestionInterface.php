<?php

namespace InformaticsCommerce\ProductAskQuestions\Api\Product;

/**
 *
 */
interface QuestionInterface
{
    /**
     *
     */
    const ID = 'id';
    /**
     *
     */
    const PRODUCT_SKU = 'product_sku';
    /**
     *
     */
    const CUSTOMER_NAME = 'customername';

    /**
     *
     */
    const EMAIL = 'email';
    /**
     *
     */
    const PHONE_NUMBER = 'phonenumber';
    /**
     *
     */
    const QUESTION = 'question';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getProductSku();

    /**
     * @return mixed
     */
    public function getCustomername();

    /**
     * @return mixed
     */
    public function getEmail();

    /**
     * @return mixed
     */
    public function getPhonenumber();

    /**
     * @return mixed
     */
    public function getQuestion();

    /**
     * @param $sku
     * @return mixed
     */
    public function setProductSku($sku);

    /**
     * @param $customername
     * @return mixed
     */
    public function setCustomername($customername);

    /**
     * @param $email
     * @return mixed
     *
     */
    public function setEmail($email);

    /**
     * @param $phonenumber
     * @return mixed
     */
    public function setPhonenumber($phonenumber);

    /**
     * @param $question
     * @return mixed
     */
    public function setQuestion($question);

}
