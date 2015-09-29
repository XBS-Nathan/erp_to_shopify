<?php

namespace ERPBundle\Consumer;

use ERPBundle\Services\Client\ErpClient;
use ERPBundle\Services\ProductCatalogService;
use ERPBundle\Services\ShopifyStoreService;
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
     * @var ShopifyStoreService
     */
    protected $store;

    /**
     * @param ErpClient $erpClient
     * @param ProductCatalogService $productCatalogService
     * @param ShopifyStoreService $storeService
     */
    public function __construct(
        ErpClient $erpClient,
        ProductCatalogService $productCatalogService,
        ShopifyStoreService $storeService
    )
    {
        $this->erpClient = $erpClient;
        $this->productCatalog = $productCatalogService;
        $this->store = $storeService;
    }

     /**
     * @param AMQPMessage $msg The message
     * @return mixed false to reject and requeue, any other value to aknowledge
     */
    public function execute(AMQPMessage $msg)
    {
        $msgBody = json_decode($msg->body);

        $catalog = $msgBody->payload->catalog;
        $storeId = $msgBody->payload->storeId;

        $store = $this->store->getStore($storeId);

        $productCatalog = $this->erpClient->getProducts($store, $catalog, true);

        $this->productCatalog->createProductsOrUpdate($productCatalog, $store);

        $this->productCatalog->addProductsToCollection($productCatalog, $store);
    }

}
