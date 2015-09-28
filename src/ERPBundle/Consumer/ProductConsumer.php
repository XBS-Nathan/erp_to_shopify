<?php

namespace ERPBundle\Consumer;

use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\ProductCatalogService;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Shopify\Client;
use OldSound\RabbitMqBundle\RabbitMq;

/**
 * Class ProductConsumer
 * @package ERPBundle\Consumer
 */
class ProductConsumer implements ConsumerInterface
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
     * @var Client
     */
    protected $shopifyClient;

    /**
     * @param ErpClient $erpClient
     * @param ProductCatalogService $productCatalogService
     * @param Client $shopifyClient
     */
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
        $msgBody = json_decode($msg->body);

        $catalog = $msgBody->payload->catalog;

        $productCatalog = $this->erpClient->getProducts($catalog, true);

        $this->productCatalog->createProductsOrUpdate($productCatalog);
    }

}
