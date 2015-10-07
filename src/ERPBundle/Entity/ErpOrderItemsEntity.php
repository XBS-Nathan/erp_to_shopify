<?php

namespace ERPBundle\Entity;


/**
 * Class ErpOrderItemsEntity
 * @package ERPBundle\Entity
 */
class ErpOrderItemEntity
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
     * @param \SimpleXMLElement $lineItem
     * @return ErpOrderItemsEntity
     */
    public static function createFromXMLObject(\SimpleXMLElement $lineItem)
    {
        $self = new self();
        $self->sku = (string) $lineItem->ItemNo;
        $self->qty = (string) $lineItem->Qty;

        return $self;

    }
}
