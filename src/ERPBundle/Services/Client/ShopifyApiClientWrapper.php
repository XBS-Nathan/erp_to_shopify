<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Services\ProductCatalogEntity;
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
        $shopifyProductsResponse = $this->shopifyClient->getProducts($limit, $page);

        $shopifyProducts = $shopifyProductsResponse->products;

        $productCatalog = [];

        foreach($shopifyProducts as $product) {
            $productCatalog[] = ProductCatalogEntity::createFromResponse($product);
        }

        return $productCatalog;
    }
}
