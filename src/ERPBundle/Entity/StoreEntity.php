<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\ERPBundle\Repository\StoreRepository")
 */
class StoreEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $storeId;

    /**
     * @ORM\Column(name="label", type="string")
     */
    private $storeLabel;

    /**
     * @ORM\Column(name="sync_products", type="integer")
     */
    private $syncProducts;

    /**
     * @ORM\Column(name="shopify_access_token", type="string")
     */
    private $shopifyAccessToken;

    /**
     * @ORM\Column(name="shopify_store_url", type="string")
     */
    private $shopifyStoreUrl;

    /**
     * @ORM\Column(name="erp_url", type="string")
     */
    private $erpUrl;

    /**
     * @ORM\Column(name="erp_username", type="string")
     */
    private $erpUsername;

    /**
     * @ORM\Column(name="erp_password", type="string")
     */
    private $erpPassword;

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getStoreLabel()
    {
        return $this->storeLabel;
    }

    public function getShopifyStoreUrl()
    {
        return $this->shopifyStoreUrl;
    }

    public function getShopifyAccessToken()
    {
        return $this->shopifyAccessToken;
    }

    /**
     * @return mixed
     */
    public function getErpUrl()
    {
        return $this->erpUrl;
    }

    /**
     * @return mixed
     */
    public function getErpUsername()
    {
        return $this->erpUsername;
    }

    /**
     * @return mixed
     */
    public function getErpPassword()
    {
        return $this->erpPassword;
    }

}
