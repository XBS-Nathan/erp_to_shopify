<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyProductEntity;
use ERPBundle\Entity\SkuToProductEntity;
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

    public function saveProduct(ErpProductEntity $erpProduct)
    {
        $productData = [
            'product' => [
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
            ]
        ];

        $response = $this->client->createProduct($productData);

        $product = ShopifyProductEntity::createFromResponse($response);

        return $product;
    }

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
}
