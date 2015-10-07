<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyOrderLineItemEntity
 * @package ERPBundle\Entity
 */
class ShopifyOrderLineItemEntity
{

    private $sku;
    private $qty;

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
     * @param $lineItem
     * @return ShopifyOrderLineItemEntity
     */
    public static function createFromResponse(array $lineItem)
    {
        $self = new self();
        $self->sku  = $lineItem['sku'];
        $self->qty = $lineItem['quantity'];
        
        return $self;
    }

}
