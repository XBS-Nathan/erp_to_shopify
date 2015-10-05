<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\ERPBundle\Repository\SkuToShopifyProductRepository")
 */
class SkuToProductEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(name="sku", type="string")
     */
    private $sku;

    /**
     * @ORM\Column(name="shopify_product_id", type="string")
     */
    private $shopifyProductId;

    /**
     * @ORM\Column(name="shopify_product_variant_id", type="string")
     */
    private $variantId;

    /**
     * @ORM\Column(name="store_id", type="string")
     */
    private $storeId;

    /**
     * @ORM\Id
     * @ORM\Column(name="catalog", type="string")
     */
    private $catalog;

    /**
     * @ORM\Column(name="last_updated_date", type="datetime")
     */
    private $lastUpdatedDate;

    /**
     * @param ErpProductEntity $erpPoduct
     * @param ShopifyProductEntity $shopifyProduct
     * @param StoreEntity $store
     * @param ProductCatalogEntity $productCatalog
     */
    public function __construct(ErpProductEntity $erpPoduct, ShopifyProductEntity $shopifyProduct, StoreEntity $store, ProductCatalogEntity $productCatalog)
    {
        $this->sku = $erpPoduct->getSku();
        $this->shopifyProductId = $shopifyProduct->getId();
        $this->variantId = $shopifyProduct->getVariantId();
        $this->storeId = $store->getStoreId();
        $this->catalog = $productCatalog->getCatalog();

        $this->lastUpdatedDate = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return mixed
     */
    public function getShopifyProductId()
    {
        return $this->shopifyProductId;
    }

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @return \Datetime
     */
    public function getLastUpdatedDate()
    {
        return $this->lastUpdatedDate;
    }

}
