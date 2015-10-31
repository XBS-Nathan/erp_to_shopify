<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpOrderEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ErpShipmentEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyOrderEntity;
use ERPBundle\Entity\ShopifyOrderLineItemEntity;
use ERPBundle\Entity\ShopifyOrderMetaFieldsEntity;
use ERPBundle\Entity\ShopifyProductEntity;
use ERPBundle\Entity\ShopifyTransactionEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\NoShopifyProductFound;
use ERPBundle\Factory\Client\ShopifyApiClientFactory;
use GuzzleHttp\Command\Exception\CommandClientException;
use Shopify\Client;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Class ShopifyApiClientWrapper
 * @package ERPBundle\Services\Client
 */
class ShopifyApiClientWrapper
{
    /**
     * @var ShopifyApiClientFactory
     */
    protected $clientFactory;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param ShopifyApiClientFactory $clientFactory
     */
    public function __construct(ShopifyApiClientFactory $clientFactory)
    {
        $this->clientFactory = $clientFactory;
    }

    /**
     * @param StoreEntity $store
     */
    public function setSettings(StoreEntity $store)
    {
        if(!$this->client) {
            $this->client = $this->clientFactory->createClient($store);
        }
    }

    /**
     * @param StoreEntity $store
     * @param CatalogEntity $catalog
     * @param $limit
     * @param $page
     * @param $collection
     * @return array
     */
    public function getProductsByCollection(StoreEntity $store, CatalogEntity $catalog, $limit, $page, &$collection)
    {
        $this->setSettings($store);

        $response = $this->client->getProducts(
            [
                'collection_id' => (int) $catalog->getShopifyCollectionId(),
                'limit' => (int) $limit,
                'page'=> $page
            ]);

        foreach($response['products'] as $product) {
            $collection[] = ShopifyProductEntity::createFromResponse($product);
        }

        return $collection;
    }

    /**
     * @param StoreEntity $store
     * @param $orderId
     * @return ShopifyOrderEntity
     */
    public function getOrder(StoreEntity $store, $orderId)
    {
        $this->setSettings($store);

        $response = $this->client->getOrder(['id' => $orderId]);

        return ShopifyOrderEntity::createFromResponse($response);
    }

    /**
     * @param StoreEntity $store
     * @return array
     */
    public function getOrders(StoreEntity $store)
    {
        $this->setSettings($store);

        $response = $this->client->getOrders();

        $orders = [];

        if(!empty($response['orders'])) {
            foreach ($response['orders'] as $order) {
                $orders[] = ShopifyOrderEntity::createFromResponse($order);
            }
        }

        return $orders;
    }

    /**
     * @param StoreEntity $store
     * @param ShopifyOrderEntity $shopifyOrder
     * @return ShopifyOrderMetaFieldsEntity
     */
    public function getOrderMetaData(StoreEntity $store, ShopifyOrderEntity $shopifyOrder)
    {
        $this->setSettings($store);

        $response = $this->client->getOrderMetaFields(['id' => $shopifyOrder->getId()]);

        return ShopifyOrderMetaFieldsEntity::createFromResponse($response);

    }

    /**
     * @param StoreEntity $store
     * @param ShopifyOrderEntity $shopifyOrder
     */
    public function completeOrder(StoreEntity $store, ShopifyOrderEntity $shopifyOrder)
    {
        $this->setSettings($store);

        $this->client->closeOrder(['id' => $shopifyOrder->getId()]);
    }

    /**
     * @param StoreEntity $store
     * @param ShopifyOrderEntity $shopifyOrder
     * @param ErpShipmentEntity $erpShipment
     * @throws \Exception
     */
    public function updateOrCreateFulfillments(StoreEntity $store, ShopifyOrderEntity $shopifyOrder, ErpShipmentEntity $erpShipment)
    {
        $this->setSettings($store);

        $fulFilledItems = [];

        /** @var ShopifyOrderLineItemEntity $item */
        foreach($shopifyOrder->getItems() as $item) {
            if($item->isFulfilled()) {
                $fulfilled          = new \stdClass();
                $fulfilled->id      = $item->getId();
                $fulFilledItems[]   = $fulfilled;
            }
        }

        if($erpShipment->getTrackingNumbers() && empty($fulFilledItems)) {
            throw new \Exception('Error: something is wrong here, got tracking numbers but no fulfilled items?');
        }

        $fulfillmentData = [
            'tracking_number' => $erpShipment->getTrackingNumbers(),
            'line_items' => $fulFilledItems
        ];

        if($shopifyOrder->getFulfillmentId()) {
            $this->client->createFulfillment(['order_id' => $shopifyOrder->getId(), 'fulfillment' => $fulfillmentData]);
        }else{
            $this->client->updateFulfillment(
                ['order_id' => $shopifyOrder->getId(), 'id' => $shopifyOrder->getFulfillmentId(), 'fulfillment' => $fulfillmentData]
            );
        }
    }

    /**
     * @param StoreEntity $store
     * @param $collectionId
     * @return ShopifyProductEntity
     */
    public function getProductCountByCollection(StoreEntity $store, $collectionId)
    {
        $this->setSettings($store);

        $response = $this->client->getProductCount(['collection_id' => $collectionId]);

        return $response['count'];
    }

    /**
     * @param StoreEntity $store
     * @param ErpProductEntity $erpProduct
     * @return ShopifyProductEntity
     */
    public function saveProduct(StoreEntity $store, ErpProductEntity $erpProduct)
    {
        $this->setSettings($store);

        $image = new \StdClass();
        $image->src =  $erpProduct->getImage();

        $productData = [
                'published' => true,
                'title' => $erpProduct->getTitle(),
                'product_type' => $erpProduct->getCategory(),
                'body_html' => $erpProduct->getFullDesription(),
                'images' => [
                    $image
                ],
                'variants' => [
                    [
                        'price' => $erpProduct->getPrice(),
                        'sku' => $erpProduct->getSku(),
                        'inventory_management' => $erpProduct->getStockManagement(),
                        'inventory_policy' => $erpProduct->getInventoryPolicy(),
                        'inventory_quantity' => $erpProduct->getQty(),
                        'fulfillment_service' => $erpProduct->getFulFilmentService()
                    ]
                ]
        ];

        $response = $this->client->createProduct(['product' => $productData]);

        $product = ShopifyProductEntity::createFromProductCreationResponse($response);

        return $product;
    }

    /**
     * @param StoreEntity $store
     * @param ErpProductEntity $erpProduct
     * @param SkuToProductEntity $skuToProductEntity
     * @return mixed
     * @throws NoShopifyProductFound
     */
    public function updateProduct(StoreEntity $store, ErpProductEntity $erpProduct, SkuToProductEntity $skuToProductEntity)
    {
        if(empty($skuToProductEntity->getVariantId())) {
            throw new \InvalidArgumentException(sprintf('Product needs a variant id before it can be updated: %s', $skuToProductEntity->getSku()));
        }

        $this->setSettings($store);

        $productData =  [
                'id' => $skuToProductEntity->getShopifyProductId(),
                'title' => $erpProduct->getTitle(),
                'product_type' => $erpProduct->getCategory(),
                'body_html' => $erpProduct->getFullDesription(),
                'variants' => [
                    [
                        'id' => $skuToProductEntity->getVariantId(),
                        'price' => $erpProduct->getPrice(),
                        'sku' => $erpProduct->getSku(),
                        'inventory_management' => $erpProduct->getStockManagement(),
                        'inventory_policy' => $erpProduct->getInventoryPolicy(),
                        'inventory_quantity' => $erpProduct->getQty()
                    ]
                ]
        ];

        try {
            $response = $this->client->updateProduct(['id' => $skuToProductEntity->getShopifyProductId(), 'product' => $productData]);
        }catch (CommandClientException $e) {
            //if 404, Collection has already been deleted via shopify, lets carry on
            if($e->getResponse()->getStatusCode() == '404') {
                throw new NoShopifyProductFound(sprintf('Product Id: %s cannot be found within shopify', $skuToProductEntity->getShopifyProductId()));
            }
        }

        return $response;
    }

    /**
     * @param StoreEntity $store
     * @param array $products
     * @param CatalogEntity $catalog
     */
    public function addProductsToCollection(StoreEntity $store, array $products, CatalogEntity $catalog)
    {
        $this->setSettings($store);
        $this->client->updateCustomCollection(['id' => $catalog->getShopifyCollectionId(), 'custom_collection' => ['id' => $catalog->getShopifyCollectionId(), 'collects' => $products]]);
    }

    /**
     * @param StoreEntity $store
     * @param CatalogEntity $catalog
     */
    public function deleteCollection(StoreEntity $store, CatalogEntity $catalog)
    {
        //Collection has already been deleted
        if(is_null($catalog->getShopifyCollectionId())) {
            return;
        }

        $this->setSettings($store);

        try {
            $this->client->deleteCustomCollection(['id' => $catalog->getShopifyCollectionId()]);
            $catalog->setShopifyCollectionId(null);
        }catch (CommandClientException $e) {
            //if 404, Collection has already been deleted via shopify, lets carry on
            if($e->getResponse()->getStatusCode() != '404') {
                throw $e;
            }
        }
    }

    /**
     * @param StoreEntity $store
     * @param CatalogEntity $catalog
     */
    public function createCollection(StoreEntity $store, CatalogEntity $catalog)
    {
        $this->setSettings($store);

        $response = $this->client->createCustomCollection(['custom_collection' => [ 'title' => $catalog->getCatalogName()]]);

        $catalog->setShopifyCollectionId($response['custom_collection']['id']);
    }

    /**
     * @param StoreEntity $store
     * @param SkuToProductEntity $skuToProductEntity
     * @return ShopifyProductEntity
     * @throws NoShopifyProductFound
     */
    public function getProduct(StoreEntity $store, SkuToProductEntity $skuToProductEntity)
    {
        $this->setSettings($store);

        try {
            $response = $this->client->getProduct(['id' => $skuToProductEntity->getShopifyProductId()]);

            return ShopifyProductEntity::createFromResponse($response);
        }catch (CommandClientException $e) {
            //if 404, Collection has already been deleted via shopify, lets carry on
            if($e->getResponse()->getStatusCode() == '404') {
                throw new NoShopifyProductFound(sprintf('Product Id: %s cannot be found within shopify', $skuToProductEntity->getShopifyProductId()));
            }
        }
    }

    /**
     * @param StoreEntity $store
     * @param ErpOrderEntity $order
     */
    public function updateOrderWithErpData(StoreEntity $store, ErpOrderEntity $order)
    {
        $this->setSettings($store);

        $erpData = new \stdClass();
        $erpData->key = 'erp-id';
        $erpData->value = $order->getOrderId();
        $erpData->value_type = 'string';
        $erpData->namespace = 'global';

        $this->client->updateOrder(
            [
                'id' => $order->getShopifyOrderId(),
                'order' => [
                    'id' => $order->getShopifyOrderId(),
                    'metafields' => [
                        $erpData
                    ]
                ]
            ]
        );
    }

    /**
     * @param StoreEntity $store
     * @throws NoShopifyProductFound
     */
    public function checkHandlingFeeProduct(StoreEntity $store)
    {
        $this->setSettings($store);

        try {
            $response = $this->client->getProduct(['id' => $store->getShopifyHandlingFeeProductId()]);
        }catch (CommandClientException $e) {
            if($e->getResponse()->getStatusCode() == '404') {
                throw new NoShopifyProductFound(sprintf('Handling fee Id: %s cannot be found within shopify', $store->getShopifyHandlingFeeProductId()));
            }
        }
    }

    /**
     * @param StoreEntity $store
     * @param ShopifyOrderEntity $order
     * @return ShopifyTransactionEntity|null
     */
    public function getTransaction(StoreEntity $store, ShopifyOrderEntity $order)
    {
        $this->setSettings($store);

        $response = $this->client->getTransactions(['order_id' => $order->getId()]);

        $orderTransaction = null;

        if($response['transactions']) {
            foreach($response['transactions'] as $transaction) {
                if(
                    $transaction['kind'] == ShopifyTransactionEntity::KIND_AUTHORIZATION ||
                    $transaction['kind'] == ShopifyTransactionEntity::KIND_CAPTURE
                ) {
                    $orderTransaction = ShopifyTransactionEntity::createFromTransactionResponse($transaction);
                    $order->setTransaction($orderTransaction);
                    break;
                }
            }
        }


    }
}
