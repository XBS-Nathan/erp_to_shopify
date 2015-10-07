<?php

namespace ERPBundle\Consumer;

use ERPBundle\Exception\ErpOrderNotFound;
use ERPBundle\Exception\ErpShipmentNotFound;
use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\Client\ShopifyApiClientWrapper;
use ERPBundle\Services\ErpShipmentService;
use ERPBundle\Services\ProductCatalogService;
use ERPBundle\Services\ShopifyStoreService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use OldSound\RabbitMqBundle\RabbitMq;

/**
 * Class ShipmentConsumer
 * @package ERPBundle\Consumer
 */
class ShipmentConsumer implements ConsumerInterface
{

    /**
     * @var ErpClient
     */
    protected $erpClient;

    /**
     * @var ProductCatalogService
     */
    protected $productCatalog;


    /**
     * @var ShopifyStoreService
     */
    protected $store;

    /**
     * @var ShopifyApiClientWrapper
     */
    protected $shopifyApiClient;

    /**
     * @param ErpClient $erpClient
     * @param ErpShipmentService $erpShipmentService
     * @param ShopifyStoreService $storeService
     * @param ShopifyApiClientWrapper $shopifyApiClientWrapper
     */
    public function __construct(
        ErpClient $erpClient,
        ErpShipmentService $erpShipmentService,
        ShopifyStoreService $storeService,
        ShopifyApiClientWrapper $shopifyApiClientWrapper
    )
    {
        $this->erpClient = $erpClient;
        $this->erpShipmentService = $erpShipmentService;
        $this->store = $storeService;
        $this->shopifyApiClient = $shopifyApiClientWrapper;
    }

     /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to aknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $msgBody = json_decode($msg->body);

        $erpOrderId = $msgBody->payload->erpOrderId;
        $shopifyOrderId = $msgBody->payload->shopifyOrderId;
        $storeId = $msgBody->payload->storeId;

        $store = $this->store->getStore($storeId);

        try {
            $order = $this->erpClient->getOrder($store, $erpOrderId);
        } catch ( ErpOrderNotFound $e) {
            return false;
        }

        try {
            $shipmentData = $this->erpClient->getShipment($store, $order);
        } catch (ErpShipmentNotFound $e) {
            return false;
        }

        $shopifyOrder = $this->shopifyApiClient->getOrder($store, $shopifyOrderId);

        $isShipmentFulfilled = $this->erpShipmentService->isShipmentFulfilled($shipmentData, $shopifyOrder);

        if($isShipmentFulfilled) {
            //Send the order off for completion
            $this->shopifyApiClient->completeOrder($store, $shopifyOrder);
        }else{
            //Send the order off to update
            $this->shopifyApiClient->updateShipping($store, $shopifyOrder);
        }

    }

}
