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

    public static function convertToXmlForErp(ShopifyOrderEntity $order)
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><Order></Order>');
        $xml->addAttribute('OrderType', 'StandAlone');

        //Get from the order
        $xml->addAttribute('CustomerID', 'MyCustomerId');

        $xml->addChild('PONumber', $order->getName());
        $xml->addChild('OrderDate', $order->getCreatedAt()->format('Y-m-d'));

        //TODO CHECK OVER
        $shippingMethod = $this->getShippingMethod($this->integration, $this->order['shipping_lines'][0]['title']);
        $xml->addChild('ShipVia', $shippingMethod);


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
        $xml->addChild('HeaderSpecialString', $this->order['shipping_lines'][0]['price'])->addAttribute('UseCode', 'ISHAMT');

        // add handling fee
        $handling_fee = 0;

        foreach ($this->order['line_items'] as $item)
        {
            if ($item['product_id'] == $this->integration->handling_fee_id)
            {
                $handling_fee = $item['quantity'] * $item['price'];
            }
        }

        $xml->addChild('HeaderSpecialString', $handling_fee)->addAttribute('UseCode', 'IHANDAMT');
        $xml->addChild('HeaderSpecialString', $order->getCreatedAt()->format('H:i:s'))->addAttribute('UseCode', 'IORDTIME');
        $xml->addChild('HeaderSpecialString', $order->getTotalTax())->addAttribute('UseCode', 'ITXAMT');

        if (isset($this->transaction->authorization))
        {
            $xml->addChild('HeaderSpecialString', $this->transaction->authorization)->addAttribute('UseCode', 'ICCAUTH');

            if (isset($this->transaction->amount))
            {
                $xml->addChild('HeaderSpecialString', $this->transaction->amount)->addAttribute('UseCode', 'ICCAMT');
            }
            else
            {
                $xml->addChild('HeaderSpecialString', 0)->addAttribute('UseCode', 'ICCAMT');
            }
        }

        foreach ($this->order['line_items'] as $item)
        {
            if ($item['product_id'] == $this->integration->handling_fee_id)
            {
                continue;
            }

            $lineItem = $xml->addChild('LineItem');
            $lineItem->addChild('LineID', $item['id']);
            $lineItem->addChild('ItemNo', $item['sku']);
            $lineItem->addChild('Qty', $item['quantity']);
            $lineItem->addChild('UnitPrice', $item['price']);

            $extPrice = $item['price'] * $item['quantity'];

            $lineItem->addChild('DetailSpecialString', $item['price'])->addAttribute('UseCode', 'ITMUNITPR');
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

        foreach($order['line_items'] as $lineItem) {
            $self->items[] = ShopifyOrderLineItemEntity::createFromResponse($lineItem);
        }

        if(!empty($order['fulfillments'])) {
            $self->fulfillmentId = $order['fulfillments'][0]['id'];
        }

        return $self;
    }

}
