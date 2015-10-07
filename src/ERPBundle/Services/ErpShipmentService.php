<?php

namespace ERPBundle\Services;

use ERPBundle\Entity\ErpOrderItemEntity;
use ERPBundle\Entity\ErpShipmentEntity;
use ERPBundle\Entity\ShopifyOrderEntity;
use ERPBundle\Entity\ShopifyOrderLineItemEntity;


/**
 * Class ErpShipmentService
 * @package ERPBundle\Services
 */
class ErpShipmentService
{

    /**
     * @param ErpShipmentEntity $erpShipment
     * @param ShopifyOrderEntity $shopifyOrder
     * @return bool
     */
    public function isShipmentFulfilled(ErpShipmentEntity $erpShipment, ShopifyOrderEntity $shopifyOrder)
    {
        $shopifyTotalQty = 0;
        $shipmentTotalQty = 0;

        /** @var ShopifyOrderLineItemEntity $lineItem */
        foreach($shopifyOrder->getItems() as $orderItem)
        {
            $shopifyTotalQty += $orderItem->getQty() ;
        }

        /** @var ErpOrderItemEntity $shippingItem */
        foreach($erpShipment->getShipmentItems() as $shippingItem)
        {
            $shipmentTotalQty += $shippingItem->getQty();
        }

        return ($shopifyTotalQty === $shipmentTotalQty) ? true : false;
    }
}
