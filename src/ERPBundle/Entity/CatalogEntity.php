<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\ERPBundle\Repository\CatalogRepository")
 */
class CatalogEntity
{
    public static $ALL = 'all';

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="catalog", type="string")
     */
    private $catalogName;

    /**
     * @ORM\Column(name="shopify_collection_id", type="integer")
     */
    private $shopifyCollectionId;

    /**
     * @ORM\Column(name="store_id", type="string")
     */
    private $storeId;


    private $createdAt;
    private $updateAt;

    /**
     * @return mixed
     */
    public function getCatalogName()
    {
        return $this->catalogName;
    }

    public function setShopifyCollectionId($collectionId)
    {
        $this->shopifyCollectionId = $collectionId;
    }

    public function getShopifyCollectionId()
    {
        return $this->shopifyCollectionId;
    }

    /**
     * @param mixed $catalogName
     */
    public function setCatalogName($catalogName)
    {
        $this->catalogName = $catalogName;
    }

    /**
     * @param mixed $storeId
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
    }


}
