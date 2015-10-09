<?php

namespace ERPBundle\Services;

use Doctrine\ORM\NoResultException;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyStoreEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\InvalidStoreName;
use ERPBundle\Repository\CatalogRepository;
use ERPBundle\Repository\SkuToShopifyProductRepository;
use ERPBundle\Repository\StoreRepository;
use ERPBundle\Services\Client\ShopifyApiClientWrapper;
use Shopify\Client;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ShopifyStoreService
 * @package ERPBundle\Services
 */
class ShopifyStoreService
{
    /**
     * @var CatalogRepository
     */
    protected $catalogRepository;

    protected $storeRepository;

    public function __construct(
        CatalogRepository $catalogRepository,
        StoreRepository $storeRepository
    )
    {
        $this->storeRepository = $storeRepository;
        $this->catalogRepository = $catalogRepository;
    }

    /**
     * @param $storeId
     * @return StoreEntity
     */
    public function getStore($storeId)
    {
        $store = $this->storeRepository->findOneByStoreId($storeId);

        if(!$store)
        {
            throw new \InvalidArgumentException(sprintf('Invalid store id: %s', $storeId));
        }

        return $store;
    }

    /**
     * @param $catalog
     * @param StoreEntity $shopifyStore
     * @return array|null|object|CatalogEntity
     * @throws NoResultException
     */
    public function getCatalog($catalog, StoreEntity $shopifyStore)
    {
        //Might not be needed this
        if($catalog == CatalogEntity::$ALL) {
            $catalogs = $this->catalogRepository->findBy(['storeId' => $shopifyStore->getStoreId()]);
        }else{
            $catalogs = $this->catalogRepository->findBy(['storeId' => $shopifyStore->getStoreId(), 'catalogName' => $catalog]);
        }

        if(!$catalogs) {
            throw new NoResultException('No catalogs found');
        }
        return $catalogs;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws InvalidStoreName
     */
    public function getStoreByRequest(Request $request)
    {
        $storeName = $request->headers->get('X-Shopify-Shop-Domain');

        if(is_null($storeName)) {
            throw new InvalidStoreName('No Store name is present');
        }

        $store = $this->storeRepository->findOnByStoreLabel($storeName);

        if(!$store)
        {
            throw new InvalidStoreName(sprintf('No store can be found with the name %s', $storeName));
        }

        return $store;
    }
}
