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

}
