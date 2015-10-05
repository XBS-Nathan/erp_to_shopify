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
     * @ORM\Column(name="shopify_product_id", type="integer")
     */
    private $shopifyProductId;

    /**
     * @ORM\Column(name="shopify_product_variant_id", type="integer")
     */
    private $variantId;

    /**
     * @ORM\Column(name="store_id", type="integer")
     */
    private $storeId;

    /**
     * @ORM\Id
     * @ORM\Column(name="catalog", type="string")
     */
    private $catalog;

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


}
