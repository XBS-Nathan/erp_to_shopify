<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyProductEntity;
use ERPBundle\Entity\SkuToProductEntity;
use GuzzleHttp\Command\Exception\CommandClientException;
use GuzzleHttp\Exception\ClientException;
use Shopify\Client;


/**
 * Class ShopifyApiClientWrapper
 * @package ERPBundle\Services\Client
 */
class ShopifyApiClientWrapper
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $page
     * @return array
     */
    public function getProducts($limit, $page)
    {
        $shopifyProductsResponse = $this->client->getProducts($limit, $page);

        $shopifyProducts = $shopifyProductsResponse->products;

        $productCatalog = [];

        foreach($shopifyProducts as $product) {
            $productCatalog[] = ProductCatalogEntity::createFromResponse($product);
        }

        return $productCatalog;
    }

    /**
     * @param ErpProductEntity $erpProduct
     * @return ShopifyProductEntity
     */
    public function saveProduct(ErpProductEntity $erpProduct)
    {
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
     * @param ErpProductEntity $erpProduct
     * @param SkuToProductEntity $skuToProductEntity
     * @return mixed
     */
    public function updateProduct(ErpProductEntity $erpProduct, SkuToProductEntity $skuToProductEntity)
    {
        if(empty($skuToProductEntity->getVariantId())) {
            throw new \InvalidArgumentException(sprintf('Product needs a variant id before it can be updated: %s', $skuToProductEntity->getSku()));
        }

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
     * @param array $products
     * @param CatalogEntity $catalog
     */
    public function addProductsToCollection(array $products, CatalogEntity $catalog)
    {
        $this->client->updateCustomCollection(['id' => $catalog->getShopifyCollectionId(), 'custom_collection' => ['collects' => $products]]);
    }

    /**
     * @param CatalogEntity $catalog
     */
    public function deleteCollection(CatalogEntity $catalog)
    {
        //Collection has already been deleted
        if(is_null($catalog->getShopifyCollectionId())) {
            return;
        }

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
     * @param CatalogEntity $catalog
     */
    public function createCollection(CatalogEntity $catalog)
    {
        $response = $this->client->createCustomCollection(['custom_collection' => [ 'title' => $catalog->getCatalogName()]]);

        $catalog->setShopifyCollectionId($response['custom_collection']['id']);
    }
}
