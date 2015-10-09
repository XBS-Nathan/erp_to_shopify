<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyCustomer
 * @package ERPBundle\Entity
 */
class ShopifyCustomer
{

    private $id;
    private $email;

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
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
