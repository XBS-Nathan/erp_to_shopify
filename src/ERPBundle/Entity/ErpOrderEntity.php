<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;


class ErpOrderEntity
{

    private $orderId;
    private $shipmentStatus;

    /**
     * @return mixed
     */
    public function getOrderId()
    {
        return $this->orderId;
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

}
