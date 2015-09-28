<?php

namespace ERPBundle\Services;

use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Repository\SkuToShopifyProductRepository;
use ERPBundle\Services\Client\ShopifyApiClientWrapper;
use Shopify\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ProductCatalogService
 * @package ERPBundle\Services\Client
 */
class ProductCatalogService
{

    /**
     * @var ShopifyApiClientWrapper
     */
    protected $shopifyClient;

    private $shopifyProductLimit;

    /**
     * @var SkuToShopifyProductRepository
     */
    protected $skuToProductRepo;

    public function __construct(
        ShopifyApiClientWrapper $shopifyClient,
        OptionsResolver $shopifyApiConfig,
        SkuToShopifyProductRepository $skuToShopifyProductRepository
    )
    {
        $this->shopifyClient = $shopifyClient;
        $this->shopifyProductLimit = '250';
        $this->skuToProductRepo = $skuToShopifyProductRepository;
    }

    /**
     * @param ProductCatalogEntity $catalog
     */
    public function createProductsOrUpdate(ProductCatalogEntity $catalog)
    {
        foreach($catalog->getProducts() as $product)
        {
            $existingProduct = $this->skuToProductRepo->findOneBySku($product->getSku());

            if(!$existingProduct)
            {
                $shopifyProduct = $this->shopifyClient->saveProduct($product);

                $skuToProduct = new SkuToProductEntity($product, $shopifyProduct);
                $this->skuToProductRepo->save($skuToProduct);
            }else{
                $this->shopifyClient->updateProduct($product, $existingProduct);
            }
        }
    }

    /**
     * @param ErpProductEntity $productEntity
     * @param SkuToProductEntity $skuToProductEntity
     */
    public function updateProduct(ErpProductEntity $productEntity, SkuToProductEntity $skuToProductEntity)
    {
        $this->shopifyClient->updateProducts($productEntity, $skuToProductEntity);
    }
}
