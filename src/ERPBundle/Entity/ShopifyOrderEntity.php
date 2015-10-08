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
        if(isset($order['order'])) {
            $order = $order['order'];
        }

        $self = new self();
        $self->id  = $order['id'];

        foreach($order['line_items'] as $lineItem) {
            $self->items[] = ShopifyOrderLineItemEntity::createFromResponse($lineItem);
        }

        if(!empty($order['fulfillments'])) {
            $self->fulfillmentId = $order['fulfillments'][0]['id'];
        }

        return $self;
    }

}
