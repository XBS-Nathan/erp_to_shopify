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
     * @return ErpOrderEntity
     */
    public static function createFromOrderXMLObject(\SimpleXMLElement $order, $orderId)
    {
        $self = new self();
        $self->orderId = $orderId;

        return $self;

    }
}
