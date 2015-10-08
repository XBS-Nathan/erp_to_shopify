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
            $shopifyTotalQty += $orderItem->getQty();
        }

        /** @var ErpOrderItemEntity $shippingItem */
        foreach($erpShipment->getShipmentItems() as $shippingItem)
        {
            $shipmentTotalQty += $shippingItem->getQty();
        }

        return ($shopifyTotalQty === $shipmentTotalQty) ? true : false;
    }

    /**
     * @param ErpShipmentEntity $erpShipment
     * @param ShopifyOrderEntity $shopifyOrder
     */
    public function setFulfilledItems(ErpShipmentEntity $erpShipment, ShopifyOrderEntity $shopifyOrder)
    {
        /** @var ErpOrderItemEntity $item */
        foreach($erpShipment->getShipmentItems() as $item) {
            /** @var ShopifyOrderLineItemEntity $shopifyItem */
            foreach($shopifyOrder->getItems() as $shopifyItem) {
                if($item->getSku() == $shopifyItem->getSku()) {
                    $shopifyItem->setIsFulfilled(1);
                }
            }
        }
    }
}
