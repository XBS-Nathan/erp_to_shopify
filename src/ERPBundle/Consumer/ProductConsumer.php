<?php

namespace ERPBundle\Consumer;

use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\ProductCatalogService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Shopify\Client;
use OldSound\RabbitMqBundle\RabbitMq;

class ProductConsumer implements ConsumerInterface
{

    protected $erpClient;

    protected $productCatalog;

    protected $shopifyClient;

    public function __construct(ErpClient $erpClient, ProductCatalogService $productCatalogService, Client $shopifyClient)
    {
        $this->erpClient = $erpClient;
        $this->productCatalog = $productCatalogService;
        $this->shopifyClient = $shopifyClient;
    }

     /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to aknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        //$msg->body being the data sent over RabbitMQ.

        $msgBody = json_decode($msg->body);

        $catalog = $msgBody->payload->catalog;

        $productCatalog = $this->erpClient->getProducts($catalog);

        $productArray = $this->productCatalog->createProducts($productCatalog);

        //Get products from ERP
        //Get What products needs to be update/created
        //Send products accordingly

        //Send notificaiton to watch tower
    }

}
