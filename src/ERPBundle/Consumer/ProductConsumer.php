<?php

namespace ERPBundle\Consumer;

use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\ProductCatalogService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Shopify\Client;

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

        $catalog = $msg->body->catalog;

        $catalogProducts = $this->erpClient->getProducts($catalog);

        $productArray = $this->productCatalog->sortProductsByCreateOrUpdate($catalogProducts);

        //Get products from ERP
        //Get What products needs to be update/created
        //Send products accordingly

        //Send notificaiton to watch tower
    }
}
