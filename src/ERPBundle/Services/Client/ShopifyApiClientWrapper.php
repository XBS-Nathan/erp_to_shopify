<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyProductEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;
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
     * @return array
     */
    public function getProductsByCollection(StoreEntity $store, CatalogEntity $catalog, $limit, $page)
    {
        $this->setSettings($store);

        $response = $this->client->getProducts(
            [
                'collection_id' => $catalog->getShopifyCollectionId(),
                'limit' => $limit,
                'page'=> $page
            ]);

        foreach($response['products'] as $product) {
            $products[] = ShopifyProductEntity::createFromResponse($product);
        }

        return $products;
    }

    /**
     * @param StoreEntity $store
     * @param $collectionId
     * @return mixed
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

        $productData = [
                'published' => true,
                'title' => $erpProduct->getTitle(),
                'product_type' => $erpProduct->getCategory(),
                'body_html' => $erpProduct->getDescription(),
                'images' => [
                    'src' => $erpProduct->getImage()
                ],
                'variants' => [
                    [
                        'price' => $erpProduct->getPrice(),
                        'sku' => $erpProduct->getSku(),
                        'inventory_management' => $erpProduct->getStockManagement(),
                        'inventory_policy' => $erpProduct->getInventoryPolicy(),
                        'inventory_quantity' => $erpProduct->getQty()
                    ]
                ]
        ];

        $response = $this->client->createProduct(['product' => $productData]);

        $product = ShopifyProductEntity::createFromResponse($response);

        return $product;
    }

    /**
     * @param StoreEntity $store
     * @param ErpProductEntity $erpProduct
     * @param SkuToProductEntity $skuToProductEntity
     * @return mixed
     */
    public function updateProduct(StoreEntity $store, ErpProductEntity $erpProduct, SkuToProductEntity $skuToProductEntity)
    {
        if(empty($skuToProductEntity->getVariantId())) {
            throw new \InvalidArgumentException(sprintf('Product needs a variant id before it can be updated: %s', $skuToProductEntity->getSku()));
        }

        $this->setSettings($store);

        $productData =  [
            'product' => [
                'id' => $skuToProductEntity->getShopifyProductId(),
                'title' => $erpProduct->getTitle(),
                'product_type' => $erpProduct->getCategory(),
                'body_html' => $erpProduct->getDescription(),
                'images' => [
                    'src' => $erpProduct->getImage()
                ],
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
            ]
        ];

        $response = $this->client->updateProduct(['id' => $skuToProductEntity->getShopifyProductId(), 'product' => $productData]);

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
        $this->client->updateCustomCollection(['id' => $catalog->getShopifyCollectionId(), 'custom_collection' => ['collects' => $products]]);
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
}
