<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class ErpShipmentEntity
{


    private $trackingNumber;

    private $shipmentItems = [];

    /**
     * @return array
     */
    public function getShipmentItems()
    {
        return $this->shipmentItems;
    }

    /**
     * @return mixed
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param \SimpleXMLElement $shipment
     * @return ErpShipmentEntity
     */
    public static function createFromShipmentXMLObject(\SimpleXMLElement $shipment)
    {
        $self = new self();

        $self->trackingNumber = (string) $shipment->PROTracking;

        foreach($shipment->Order->Pack as $box)
        {
            foreach($box->LineItem as $item) {
                $self->shipmentItems[] = ErpOrderItemEntity::createFromXMLObject($item);
            }
        }

        return $self;

    }
}
