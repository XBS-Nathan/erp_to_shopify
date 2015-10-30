<?php

namespace ERPBundle\Services;

use Doctrine\ORM\NoResultException;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyProductEntity;
use ERPBundle\Entity\ShopifyStoreEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\NoShopifyProductFound;
use ERPBundle\Repository\CatalogRepository;
use ERPBundle\Repository\SkuToShopifyProductRepository;
use ERPBundle\Repository\StoreRepository;
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

    private $shopifyProductLimit = 250;

    /**
     * @var SkuToShopifyProductRepository
     */
    protected $skuToProductRepo;

    /**
     * @var CatalogRepository
     */
    protected $catalogRepository;

    /**
     * @var StoreRepository
     */
    protected $storeRepository;

    /**
     * @param ShopifyApiClientWrapper $shopifyClient
     * @param OptionsResolver $shopifyApiConfig
     * @param SkuToShopifyProductRepository $skuToShopifyProductRepository
     * @param CatalogRepository $catalogRepository
     * @param StoreRepository $storeRepository
     */
    public function __construct(
        ShopifyApiClientWrapper $shopifyClient,
        OptionsResolver $shopifyApiConfig,
        SkuToShopifyProductRepository $skuToShopifyProductRepository,
        CatalogRepository $catalogRepository,
        StoreRepository $storeRepository
    )
    {
        $this->shopifyClient = $shopifyClient;
        $this->skuToProductRepo = $skuToShopifyProductRepository;
        $this->catalogRepository = $catalogRepository;
        $this->storeRepository = $storeRepository;
    }


    /**
     * @param CatalogEntity $catalogEntity
     * @param StoreEntity $storeEntity
     * @param ProductCatalogEntity $productCatalogEntity
     */
    public function collectProductsFromShopifyAndImport(
        CatalogEntity $catalogEntity,
        StoreEntity $storeEntity,
        ProductCatalogEntity $productCatalogEntity
    ) {
        $totalProducts = $this->shopifyClient->getProductCountByCollection($storeEntity, $catalogEntity->getShopifyCollectionId() );

        if($totalProducts == 0) return;

        $totalPages = ceil($totalProducts / $this->shopifyProductLimit);

        $products= [];

        for($currentPage=1; $currentPage <= $totalPages; $currentPage++) {
            $this->shopifyClient->getProductsByCollection(
                $storeEntity, $catalogEntity, $this->shopifyProductLimit, $currentPage, $products
            );
        }

        //Check to see if the products are in the database
        /** @var ShopifyProductEntity $shopifyProduct */
        foreach($products as $shopifyProduct) {

            /** @var SkuToProductEntity $existingProduct */
            $existingProduct = $this->skuToProductRepo->findOneBy(
                [
                    'shopifyProductId' => $shopifyProduct->getId(),
                    'storeId' => $storeEntity->getStoreId(),
                    'catalog' => $catalogEntity->getCatalogName()
                ]
            );

            if (!$existingProduct) {
                /** @var ErpProductEntity $product */
                foreach ($productCatalogEntity->getProducts() as $product) {
                    if ($product->getSku() == $shopifyProduct->getSku()) {
                        $skuToProduct = new SkuToProductEntity($product, $shopifyProduct, $storeEntity, $productCatalogEntity);
                        $this->skuToProductRepo->save($skuToProduct);
                    }
                }
            }
        }

        $this->removeMissingShopifyProducts($storeEntity, $catalogEntity, $products);

    }


    /**
     * @param StoreEntity $storeEntity
     * @param CatalogEntity $catalogEntity
     * @param array $shopifyProducts
     */
    private function removeMissingShopifyProducts(StoreEntity $storeEntity, CatalogEntity $catalogEntity, array $shopifyProducts)
    {
        if(empty($shopifySkus)) return;

        $existingProducts = $this->skuToProductRepo->findBy( [
            'storeId' => $storeEntity->getStoreId(),
            'catalog' => $catalogEntity->getCatalogName()
        ]);

        /** @var ShopifyProductEntity $shopifyProduct */
        foreach($shopifyProducts as $shopifyProduct)
        {
            $shopifySkus[] = $shopifyProduct->getSku();
        }

        /** @var SkuToProductEntity $product */
        foreach($existingProducts as $product) {
            if(!in_array($product->getSku(), $shopifySkus)) {
                $this->skuToProductRepo->remove($product);
            }
        }

        $this->skuToProductRepo->flush();
    }


    /**
     * @param ProductCatalogEntity $catalog
     * @param StoreEntity $store
     */
    public function createProductsOrUpdate(ProductCatalogEntity $catalog, StoreEntity $store)
    {
        /** @var ErpProductEntity $product */
        foreach($catalog->getProducts() as $product)
        {
            /** @var SkuToProductEntity $existingProduct */
            $existingProduct = $this->skuToProductRepo->findOneBy(
                ['sku' => $product->getSku(), 'storeId' => $store->getStoreId(), 'catalog' => $catalog->getCatalog()]
            );

            if(!$existingProduct)
            {
                $shopifyProduct = $this->shopifyClient->saveProduct($store, $product);

                $skuToProduct = new SkuToProductEntity($product, $shopifyProduct, $store, $catalog);
                $this->skuToProductRepo->save($skuToProduct);
            }else{
                $lastUpdateData = $existingProduct->getLastUpdatedDate();

                try {

                    $shopifyProduct = $this->shopifyClient->getProduct($store, $existingProduct);

                    if(
                        ($lastUpdateData->getTimestamp() <= $product->getLastUpdated()->getTimeStamp())
                        || ($product->getQty() < $shopifyProduct->getQty())
                        || ($product->getQty() > $shopifyProduct->getQty())
                    ){
                        $this->shopifyClient->updateProduct($store, $product, $existingProduct);
                    }

                }catch (NoShopifyProductFound $e) {
                    //Lets recreate this within shopify
                    $shopifyProduct = $this->shopifyClient->saveProduct($store, $product);

                    $existingProduct->updateShopifyIds($shopifyProduct);
                    $this->skuToProductRepo->update($existingProduct);
                }

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

        if(!$catalog) {
            throw new \InvalidArgumentException(sprintf('Cannot find catalog %s', $productCatalog->getCatalog()));
        }

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
                ['sku' => $product->getSku(), 'storeId' => $store->getStoreId(), 'catalog' => $productCatalog->getCatalog()]
            );

            if($existingProduct) {
                $products[] = ['product_id' => $existingProduct->getShopifyProductId()];
            }
        }

        $this->shopifyClient->addProductsToCollection($store, $products, $catalog);
    }

    /**
     * @param StoreEntity $store
     */
    public function checkHandlingFeeProductAndCreateIt(StoreEntity $store)
    {
        try {

            if($store->getShopifyHandlingFeeProductId()) {
                $this->shopifyClient->checkHandlingFeeProduct($store);
            }else{
                $erpProduct = ErpProductEntity::createHandlingFeeProduct();
                $product = $this->shopifyClient->saveProduct($store, $erpProduct);

                $store->setShopifyHandlingFeeProductId($product->getId());

                $this->storeRepository->save($store);
            }

        }catch(NoShopifyProductFound $e) {
            //Add the handling fee product
            $this->shopifyClient->saveProduct($store);
            $this->storeRepository->save($store);
        }
    }

}
