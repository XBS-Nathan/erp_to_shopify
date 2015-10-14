<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyOrderShippingEntity
 * @package ERPBundle\Entity
 */
class ShopifyOrderShippingEntity
{

    private $price;
    private $title;

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param array $transaction
     * @return ShopifyTransactionEntity
     */
    public static function createFromOrderResponse(array $transaction)
    {
        $self = new self();
        $self->price = $transaction[0]['price'];
        $self->title = $transaction[0]['title'];

        return $self;
    }

}
