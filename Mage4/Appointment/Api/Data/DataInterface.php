<?php

namespace Mage4\Appointment\Api\Data;

interface DataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID                    = 'id';
    const FIRSTNAME             = 'firstname';
    const LASTNAME              = 'lastname';
    const PHONE                 = 'phone';
    const EMAIL                 = 'email';
    const COMMENT               = 'comment';
    const CREATED_AT            = 'created_at';



    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID
     *
     * @param $id
     * @return DataInterface
     */
    public function setId($id);

    /**
     * Get FirstName
     *
     * @return string
     */
    public function getFirstName();

    /**
     * Set FirstName
     *
     * @param $firstname
     * @return mixed
     */
    public function setFirstName($firstname);

/**
     * Get LastName
     *
     * @return string
     */
    public function getLastName();

    /**
     * Set LastName
     *
     * @param $lastname
     * @return mixed
     */
    public function setLastName($lastname);

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();

    /**
     * Set Email
     *
     * @param $email
     * @return mixed
     */
    public function setEmail($email);

    /**
     * Get Phone
     *
     * @return string
     */
    public function getPhone();

    /**
     * Set Phone
     *
     * @param $phone
     * @return mixed
     */
    public function setPhone($phone);

    /**
     * Get Address
     *
     * @return mixed
     */
    public function getAddress();

    /**
     * Set Address
     *
     * @param $comment
     * @return mixed
     */
    public function setAddress($address);
    
    /**
     * Get Comment
     *
     * @return mixed
     */
    public function getComment();

    /**
     * Set Comment
     *
     * @param $comment
     * @return mixed
     */
    public function setComment($comment);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return DataInterface
     */
    public function setCreatedAt($createdAt);
}
