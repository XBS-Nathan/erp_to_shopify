<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyOrderLineItemEntity
 * @package ERPBundle\Entity
 */
class ShopifyOrderLineItemEntity
{

    private $id;
    private $sku;
    private $qty;
    private $isFulfilled;
    private $price;
    private $variantId;

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @return mixed
     */
    public function isFulfilled()
    {
        return $this->isFulfilled;
    }

    /**
     * @param integer $isFulfilled
     */
    public function setIsFulfilled($isFulfilled)
    {
        $this->isFulfilled = $isFulfilled;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param $lineItem
     * @return ShopifyOrderLineItemEntity
     */
    public static function createFromResponse(array $lineItem)
    {
        $self = new self();
        $self->sku = $lineItem['sku'];
        $self->qty = $lineItem['quantity'];
        $self->id = $lineItem['id'];
        $self->variantId = $lineItem['variant_id'];
        $self->price = $lineItem['price'];
        
        return $self;
    }

}
