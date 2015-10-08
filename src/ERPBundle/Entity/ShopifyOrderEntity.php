<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyOrderEntity
 * @package ERPBundle\Entity
 */
class ShopifyOrderEntity
{

    private $id;

    private $items;

    private $fulfillmentId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return mixed
     */
    public function getFulfillmentId()
    {
        return $this->fulfillmentId;
    }

    /**
     * @param $order
     * @return ShopifyOrderEntity
     */
    public static function createFromResponse($order)
    {
        $self = new self();
        $self->id  = $order['order']['id'];

        foreach($order['order']['line_items'] as $lineItem) {
            $self->items[] = ShopifyOrderLineItemEntity::createFromResponse($lineItem);
        }

        $self->fulfillmentId = $order['order']['fulfillments'][0]['id'];

        return $self;
    }

}
