<?php

namespace Mage4\Appointment\Model;

use Magento\Framework\Model\AbstractModel;
use Mage4\Appointment\Api\Data\DataInterface;

class Data extends AbstractModel implements DataInterface
{
    /**
     * Cache tag
     */
    const CACHE_TAG = 'Mage4_Appointment';

    /**
     * Initialise resource model
     * @codingStandardsIgnoreStart
     */
    protected function _construct()
    {
        // @codingStandardsIgnoreEnd
        $this->_init('Mage4\Appointment\Model\ResourceModel\Data');
    }

    /**
     * Get cache identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get Id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getData(DataInterface::ID);
    }

    /**
     * Set Id
     *
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(DataInterface::ID, $id);
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->getData(DataInterface::FIRSTNAME);
    }

    /**
     * Set firstname
     *
     * @param $firstname
     * @return $this
     */
    public function setFirstName($firstname)
    {
        return $this->setData(DataInterface::FIRSTNAME, $firstname);
    }

    /**
     * Get last name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->getData(DataInterface::LASTNAME);
    }

    /**
     * Set last name
     *
     * @param $lastname
     * @return $this
     */
    public function setLastName($lastname)
    {
        return $this->setData(DataInterface::LASTNAME, $lastname);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(DataInterface::EMAIL);
    }

    /**
     * Set email
     *
     * @param $email
     * @return $this
     */
    public function setEmail($email)
    {
        return $this->setData(DataInterface::EMAIL, $email);
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData(DataInterface::PHONE);
    }

    /**
     * Set phone
     *
     * @param $phone
     * @return $this
     */
    public function setPhone($phone)
    {
        return $this->setData(DataInterface::PHONE, $phone);
    }

     /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getData(DataInterface::COMMENT);
    }

    /**
     * Set address
     *
     * @param $address
     * @return $this
     */
    public function setAddress($address)
    {
        return $this->setData(DataInterface::COMMENT, $address);
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(DataInterface::COMMENT);
    }

    /**
     * Set comment
     *
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        return $this->setData(DataInterface::COMMENT, $comment);
    }

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(DataInterface::CREATED_AT);
    }

    /**
     * Set created at
     *
     * @param $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(DataInterface::CREATED_AT, $createdAt);
    }
}
