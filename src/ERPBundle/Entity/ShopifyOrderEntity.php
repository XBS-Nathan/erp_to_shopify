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

        $bill = $xml->addChild('BillTo');
        $bill->addChild('Name', $this->order['billing_address']['name']);
        $bill->addChild('Address1', $this->order['billing_address']['address1']);
        $bill->addChild('Address2', $this->order['billing_address']['address2']);
        $bill->addChild('City', $this->order['billing_address']['city']);
        $bill->addChild('State', $this->order['billing_address']['province_code']);
        $bill->addChild('PostalCode', $this->order['billing_address']['zip']);
        $bill->addChild('ContactName', $this->order['billing_address']['name']);
        $bill->addChild('ContactPhone', $this->order['billing_address']['phone']);
        $bill->addChild('ContactEmail', $this->order['customer']['email']);

        $ship = $xml->addChild('ShipTo');
        $ship->addChild('Name', $this->order['shipping_address']['name']);
        $ship->addChild('Address1', $this->order['shipping_address']['address1']);
        $ship->addChild('Address2', $this->order['shipping_address']['address2']);
        $ship->addChild('City', $this->order['shipping_address']['city']);
        $ship->addChild('State', $this->order['shipping_address']['province_code']);
        $ship->addChild('PostalCode', $this->order['shipping_address']['zip']);
        $ship->addChild('ContactName', $this->order['shipping_address']['name']);
        $ship->addChild('ContactPhone', $this->order['shipping_address']['phone']);
        $ship->addChild('ContactEmail', $this->order['customer']['email']);

        $xml->addChild('HeaderSpecialString', $this->order['customer']['id'])->addAttribute('UseCode', 'IUSERID');
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
        $xml->addChild('HeaderSpecialString', date_create_from_format( "Y-m-d\TH:i:se" , $this->order['created_at'] )->format('H:i:s'))->addAttribute('UseCode', 'IORDTIME');
        $xml->addChild('HeaderSpecialString', $this->order['total_tax'])->addAttribute('UseCode', 'ITXAMT');

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

        foreach($order['line_items'] as $lineItem) {
            $self->items[] = ShopifyOrderLineItemEntity::createFromResponse($lineItem);
        }

        if(!empty($order['fulfillments'])) {
            $self->fulfillmentId = $order['fulfillments'][0]['id'];
        }

        return $self;
    }

}
