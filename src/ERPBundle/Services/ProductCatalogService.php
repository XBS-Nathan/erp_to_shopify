<?php

namespace ERPBundle\Services;

use Doctrine\ORM\NoResultException;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyStoreEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Repository\CatalogRepository;
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

    /**
     * @var CatalogRepository
     */
    protected $catalogRepository;

    /**
     * @param ShopifyApiClientWrapper $shopifyClient
     * @param OptionsResolver $shopifyApiConfig
     * @param SkuToShopifyProductRepository $skuToShopifyProductRepository
     * @param CatalogRepository $catalogRepository
     */
    public function __construct(
        ShopifyApiClientWrapper $shopifyClient,
        OptionsResolver $shopifyApiConfig,
        SkuToShopifyProductRepository $skuToShopifyProductRepository,
        CatalogRepository $catalogRepository
    )
    {
        $this->shopifyClient = $shopifyClient;
        $this->shopifyProductLimit = '250';
        $this->skuToProductRepo = $skuToShopifyProductRepository;
        $this->catalogRepository = $catalogRepository;
    }

    /**
     * @param ProductCatalogEntity $catalog
     * @param StoreEntity $store
     */
    public function createProductsOrUpdate(ProductCatalogEntity $catalog, StoreEntity $store)
    {
        foreach($catalog->getProducts() as $product)
        {
            $existingProduct = $this->skuToProductRepo->findOneBy(
                ['sku' => $product->getSku(), 'storeId' => $store->getStoreId()]
            );

            if(!$existingProduct)
            {
                $shopifyProduct = $this->shopifyClient->saveProduct($store, $product);

                $skuToProduct = new SkuToProductEntity($product, $shopifyProduct, $store);
                $this->skuToProductRepo->save($skuToProduct);
            }else{
                $this->shopifyClient->updateProduct($store, $product, $existingProduct);
            }
        }
    }

    /**
     * @param ProductCatalogEntity $productCatalog
     * @param StoreEntity $store
     */
    public function addProductsToCollection(ProductCatalogEntity $productCatalog, StoreEntity $store)
    {
        /** @var CatalogEntity $catalog */
        $catalog = $this->catalogRepository->findOneBy(
            ['storeId' => $store->getStoreId(), 'catalogName' => $productCatalog->getCatalog()]
        );

        //Process is to delete the collection and then recreate it as we cannot remove products
        //from a collection that easy.
        $this->shopifyClient->deleteCollection($store, $catalog);
        $this->catalogRepository->save($catalog);

        $this->shopifyClient->createCollection($store, $catalog);
        $this->catalogRepository->save($catalog);

        $products = [];

        foreach($productCatalog->getProducts() as $product) {
            /** @var SkuToProductEntity $existingProduct */
            $existingProduct = $this->skuToProductRepo->findOneBy(
                ['sku' => $product->getSku(), 'storeId' => $store->getStoreId()]
            );

            if($existingProduct) {
                $products[] = ['product_id' => $existingProduct->getShopifyProductId()];
            }
        }

        $this->shopifyClient->addProductsToCollection($store, $products, $catalog);
    }

}
