<?php

namespace ERPBundle\Webhook\Handler;

use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\Client\ShopifyApiClientWrapper;
use ERPBundle\Webhook\Command\BaseCommand;

/**
 * Class CreateOrderHandler
 * @package ERPBundle\Webhook\Handler
 */
class CreateOrderHandler extends BaseHandler
{

    /**
     * @param ErpClient $client
     * @param ShopifyApiClientWrapper $shopifyClient
     */
    public function __construct(
        ErpClient $client,
        ShopifyApiClientWrapper $shopifyClient
    ){
        $this->client = $client;
        $this->shopifyClient = $shopifyClient;
    }

    public function execute(BaseCommand $cmd)
    {
        $order = $cmd->getOrder();
        $store = $cmd->getStore();


        $this->shopifyClient->getTransaction($store, $order);

        $erpOrder = $this->client->createOrder($store, $order);

        $this->shopifyClient->updateOrderWithErpData($store, $erpOrder);

    }
}
