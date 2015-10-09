<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyProductEntity
 * @package ERPBundle\Entity
 */
class ShopifyCustomerAddress
{

    private $name;
    private $addressLine1;
    private $addressLine2;
    private $city;
    private $provinceCode;
    private $postalCode;
    private $contactName;
    private $contactPhone;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return mixed
     */
    public function getProvinceCode()
    {
        return $this->provinceCode;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * @return mixed
     */
    public function getContactPhone()
    {
        return $this->contactPhone;
    }


    /**
     * @param array $address
     * @return ShopifyCustomerAddress
     */
    public static function createFromOrderResponse(array $address)
    {
        $self               = new self();
        $self->name         = $address['name'];
        $self->addressLine1 = $address['address1'];
        $self->addressLine2 = $address['address2'];
        $self->city         = $address['city'];
        $self->provinceCode = $address['province_code'];
        $self->postalCode   = $address['zip'];
        $self->contactName  = $address['first_name'] .' '. $address['last_name'];
        $self->contactPhone = $address['phone'];

        return $self;
    }

}
