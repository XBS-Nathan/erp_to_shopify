<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class ErpShipmentEntity
 * @package ERPBundle\Entity
 */
class ErpShipmentEntity
{
    /**
     * @var array
     */
    private $trackingNumbers = [];

    /**
     * @var array
     */
    private $shipmentItems = [];

    /**
     * @return array
     */
    public function getShipmentItems()
    {
        return $this->shipmentItems;
    }

    /**
     * @return array
     */
    public function getTrackingNumbers()
    {
        return $this->trackingNumbers;
    }

    /**
     * @param \SimpleXMLElement $shipment
     * @return ErpShipmentEntity
     */
    public static function createFromShipmentXMLObject(\SimpleXMLElement $shipment)
    {
        $self = new self();

        foreach($shipment->Order->Pack as $box)
        {
            foreach($box->LineItem as $item) {
                $self->shipmentItems[] = ErpOrderItemEntity::createFromXMLObject($item);
            }

            $self->trackingNumbers[] = (string) $box->TrackingNo;
        }

        return $self;
    }
}
