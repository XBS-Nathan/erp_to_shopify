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
     * @param array $customer
     * @return ShopifyCustomer
     */
    public static function createFromOrderResponse(array $customer)
    {
        $self               = new self();
        $self->id         = $customer['id'];
        $self->email        = $customer['email'];

        return $self;
    }

}
