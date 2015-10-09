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

    /**
     * @ORM\Column(name="shopify_secret_token", type="string")
     */
    private $shopifySecretToken;

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
    public function getShopifySecretToken()
    {
        return $this->shopifySecretToken;
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

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }

    /**
     * @param mixed $storeLabel
     */
    public function setStoreLabel($storeLabel)
    {
        $this->storeLabel = $storeLabel;
    }

    /**
     * @param mixed $syncProducts
     */
    public function setSyncProducts($syncProducts)
    {
        $this->syncProducts = $syncProducts;
    }

    /**
     * @param mixed $shopifyAccessToken
     */
    public function setShopifyAccessToken($shopifyAccessToken)
    {
        $this->shopifyAccessToken = $shopifyAccessToken;
    }

    /**
     * @param mixed $shopifyStoreUrl
     */
    public function setShopifyStoreUrl($shopifyStoreUrl)
    {
        $this->shopifyStoreUrl = $shopifyStoreUrl;
    }

    /**
     * @param mixed $erpUrl
     */
    public function setErpUrl($erpUrl)
    {
        $this->erpUrl = $erpUrl;
    }

    /**
     * @param mixed $erpUsername
     */
    public function setErpUsername($erpUsername)
    {
        $this->erpUsername = $erpUsername;
    }

    /**
     * @param mixed $erpPassword
     */
    public function setErpPassword($erpPassword)
    {
        $this->erpPassword = $erpPassword;
    }


}
