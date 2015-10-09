<?php

namespace ERPBundle\Webhook\Handler;

use ERPBundle\Services\Client\ErpClient;

/**
 * Class CreateOrderHandler
 * @package ERPBundle\Webhook\Handler
 */
class CreateOrderHandler extends BaseHandler
{

    /**
     * @param ErpClient $client
     */
    public function __construct(
        ErpClient $client
    ){
        $this->client = $client;
    }

    public function execute(BaseCommand $cmd)
    {
        $order = $cmd->getOrder();
        $store = $cmd->getStore();

        $this->client->createOrder($store, $order);
    }
}
