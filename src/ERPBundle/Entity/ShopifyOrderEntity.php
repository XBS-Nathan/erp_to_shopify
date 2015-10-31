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

    private $createdAt;

    private $name;

    private $billingAddress;

    private $shippingAddress;

    private $customer;

    private $totalTax;

    /**
     * @var ShopifyTransactionEntity
     */
    private $transaction;

    private $shipping;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \ERPBundle\Entity\ShopifyOrderLineItemEntity|array
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
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return ShopifyCustomerAddress
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * @return ShopifyCustomer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return ShopifyCustomerAddress
     */
    public function getShippingAddress()
    {
        return $this->shippingAddress;
    }

    /**
     * @return mixed
     */
    public function getTotalTax()
    {
        return $this->totalTax;
    }

    /**
     * @return ShopifyTransactionEntity
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @return ShopifyOrderShippingEntity
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param ShopifyTransactionEntity $transaction
     */
    public function setTransaction(ShopifyTransactionEntity $transaction)
    {
        $this->transaction = $transaction;
    }


    /**
     * @param ShopifyOrderEntity $order
     * @param StoreEntity $store
     * @return SimpleXMLElement
     */
    public static function convertToXmlForErp(ShopifyOrderEntity $order, StoreEntity $store)
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><Order></Order>');
        $xml->addAttribute('OrderType', 'StandAlone');

        //Get from the order
        $xml->addAttribute('CustomerID', $store->getErpCustomerId());

        $xml->addChild('PONumber', $order->getName());
        $xml->addChild('OrderDate', $order->getCreatedAt()->format('Y-m-d'));

        $orderShipping = $order->getShipping();

        if ($store->getShipmentMaps())
        {
            /** @var ErpShipmentMapEntity $map */
            foreach ($store->getShipmentMaps() as $map)
            {
                if ($map->getErpMethod() == $orderShipping->getTitle())
                {
                    $xml->addChild('ShipVia', $map->getErpMethod());
                    break;
                }
            }
        }

        $billingAddress = $order->getBillingAddress();

        $bill = $xml->addChild('BillTo');
        $bill->addChild('Name', $billingAddress->getName());
        $bill->addChild('Address1', $billingAddress->getAddressLine1());
        $bill->addChild('Address2', $billingAddress->getAddressLine2());
        $bill->addChild('City', $billingAddress->getCity());
        $bill->addChild('State', $billingAddress->getProvinceCode());
        $bill->addChild('PostalCode', $billingAddress->getPostalCode());
        $bill->addChild('ContactName', $billingAddress->getContactName());
        $bill->addChild('ContactPhone', $billingAddress->getContactPhone());

        $customer = $order->getCustomer();

        $bill->addChild('ContactEmail', $customer->getEmail());

        $shippingAddress = $order->getShippingAddress();

        $ship = $xml->addChild('ShipTo');
        $ship->addChild('Name', $shippingAddress->getName());
        $ship->addChild('Address1', $shippingAddress->getAddressLine1());
        $ship->addChild('Address2', $shippingAddress->getAddressLine2());
        $ship->addChild('City', $shippingAddress->getCity());
        $ship->addChild('State', $shippingAddress->getProvinceCode());
        $ship->addChild('PostalCode', $shippingAddress->getPostalCode());
        $ship->addChild('ContactName', $shippingAddress->getContactName());
        $ship->addChild('ContactPhone', $shippingAddress->getContactPhone());

        $ship->addChild('ContactEmail', $customer->getEmail());

        $xml->addChild('HeaderSpecialString', $customer->getId())->addAttribute('UseCode', 'IUSERID');

        $xml->addChild('HeaderSpecialString', $orderShipping->getPrice())->addAttribute('UseCode', 'ISHAMT');

        // add handling fee
        $handlingFee = 0;

        /** @var \ERPBundle\Entity\ShopifyOrderLineItemEntity $item */
        foreach($order->getItems() as $item)
        {
            if ($item->getProductId() == $store->getShopifyHandlingFeeProductId())
            {
                $handlingFee = $item['quantity'] * $item['price'];
            }
        }

        $xml->addChild('HeaderSpecialString', $handlingFee)->addAttribute('UseCode', 'IHANDAMT');
        $xml->addChild('HeaderSpecialString', $order->getCreatedAt()->format('H:i:s'))->addAttribute('UseCode', 'IORDTIME');
        $xml->addChild('HeaderSpecialString', $order->getTotalTax())->addAttribute('UseCode', 'ITXAMT');

        $transaction = $order->getTransaction();

        if ($transaction && $transaction->getAuthorization())
        {
            $xml->addChild('HeaderSpecialString', $transaction->getAuthorization())->addAttribute('UseCode', 'ICCAUTH');

            if ($transaction->getAmount())
            {
                $xml->addChild('HeaderSpecialString', $transaction->getAmount())->addAttribute('UseCode', 'ICCAMT');
            }
            else
            {
                $xml->addChild('HeaderSpecialString', 0)->addAttribute('UseCode', 'ICCAMT');
            }
        }

        /** @var \ERPBundle\Entity\ShopifyOrderLineItemEntity $item */
        foreach ($order->getItems() as $item)
        {
            if ($item->getProductId() == $store->getShopifyHandlingFeeProductId())
            {
                continue;
            }

            $lineItem = $xml->addChild('LineItem');
            $lineItem->addChild('LineID', $item->getId());
            $lineItem->addChild('ItemNo', $item->getSku());
            $lineItem->addChild('Qty', $item->getQty());
            $lineItem->addChild('UnitPrice', $item->getPrice());

            $extPrice = $item->getPrice() * $item->getQty();

            $lineItem->addChild('DetailSpecialString', $item->getPrice())->addAttribute('UseCode', 'ITMUNITPR');
            $lineItem->addChild('DetailSpecialString', $extPrice)->addAttribute('UseCode', 'ITMEXTPR');
        }

        return $xml;
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
        $self->createdAt = new \DateTime($order['created_at']);
        $self->name = $order['name'];
        $self->billingAddress = ShopifyCustomerAddress::createFromOrderResponse($order['billing_address']);
        $self->shippingAddress = ShopifyCustomerAddress::createFromOrderResponse($order['shipping_address']);
        $self->customer = ShopifyCustomer::createFromOrderResponse($order['customer']);

        $self->totalTax = $order['total_tax'];

        if(isset($order['transaction'])) {
            $self->transaction = ShopifyTransactionEntity::createFromOrderResponse($order['transaction']);
        }
        $self->shipping = ShopifyOrderShippingEntity::createFromOrderResponse($order['shipping_lines']);


        foreach($order['line_items'] as $lineItem) {
            $self->items[] = ShopifyOrderLineItemEntity::createFromResponse($lineItem);
        }

        if(!empty($order['fulfillments'])) {
            $self->fulfillmentId = $order['fulfillments'][0]['id'];
        }

        return $self;
    }

}
