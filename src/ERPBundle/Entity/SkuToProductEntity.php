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
     * @ORM\Id
     * @ORM\Column(name="shopify_product_id", type="string")
     */
    private $shopifyProductId;

    /**
     * @ORM\Id
     * @ORM\Column(name="shopify_product_variant_id", type="string")
     */
    private $variantId;

    /**
     * @ORM\Id
     * @ORM\Column(name="store_id", type="string")
     */
    private $storeId;

    /**
     * @param ErpProductEntity $erpPoduct
     * @param ShopifyProductEntity $shopifyProduct
     * @param StoreEntity $store
     */
    public function __construct(ErpProductEntity $erpPoduct, ShopifyProductEntity $shopifyProduct, StoreEntity $store)
    {
        $this->sku = $erpPoduct->getSku();
        $this->shopifyProductId = $shopifyProduct->getId();
        $this->variantId = $shopifyProduct->getVariantId();
        $this->storeId = $store->getStoreId();
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
