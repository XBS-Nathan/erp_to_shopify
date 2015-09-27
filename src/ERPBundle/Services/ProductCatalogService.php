<?php

namespace ERPBundle\Services;

use ERPBundle\Entity\ProductCatalogEntity;
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

    public function createProducts(ProductCatalogEntity $catalog)
    {
        foreach($catalog->getProducts() as $product)
        {
            $existingProduct = $this->skuToProductRepo->findOneBySku($product->getSku());

            if(!$existingProduct)
            {
                $this->shopifyClient->saveProduct($product);
            }
        }



    }

    public function sortProductsByCreateOrUpdate(ProductCatalogEntity $catalog)
    {
        //Get shopify product count
        $productCount = $this->shopifyClient->getProductCount();

        $totalPages = $productCount / $this->shopifyProductLimit;
        $erpSKUs = [];

        foreach($products as $product) {
            $erpSKUs[$product->getId()] = $product->getSku();
        }

        if(empty($erpSKUs)) {
            return $products;
        }

        for($currentPage=1; $currentPage<=$totalPages; $currentPage++)
        {
            $shopifyProducts = $this->shopifyClient->getProducts($this->shopifyProductLimit, $currentPage);

            $this->checkProductAgainstErp($shopifyProducts, $erpSKUs);
        }

        return $products;

    }

    private function checkProductAgainstErp(array $shopifyProducts, array $erpSKUs)
    {
        /** @var ShopifyProductEntity $product */
        foreach($shopifyProducts as $product)
        {
            if(in_array($product->getSku(), $erpSKUs)) {

            }
        }
    }

}
