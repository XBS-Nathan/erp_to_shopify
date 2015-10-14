<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


class ErpOrderEntity
{

    private $orderId;
    private $shipmentStatus;
    private $shopifyOrderId;

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return mixed
     */
    public function getShopifyOrderId()
    {
        return $this->shopifyOrderId;
    }



    /**
     * @param \SimpleXMLElement $order
     * @param $orderId
     * @return ErpOrderEntity
     */
    public static function createFromOrderXMLObject(\SimpleXMLElement $order, $orderId = null)
    {
        $self = new self();

        if(is_null($orderId)) {
            $orderId = $order->OrderNumber;
        }
        $self->orderId = $orderId;

        return $self;

    }

    /**
     * @param \SimpleXMLElement $order
     * @param ShopifyOrderEntity $shopifyOrder
     * @return ErpOrderEntity
     */
    public static function createFromShopifyOrder(\SimpleXMLElement $order, ShopifyOrderEntity $shopifyOrder)
    {
        $self = new self();
        $self->orderId = (string) $order->OrderNumber;
        $self->shopifyOrderId = $shopifyOrder->getId();

        return $self;
    }

}
