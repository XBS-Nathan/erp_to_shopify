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
     * @ORM\Column(name="id", type="string")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="catalog", type="string")
     */
    private $catalogName;

    /**
     * @ORM\Column(name="shopify_collection_id", type="string")
     */
    private $shopifyCollectionId;

    /**
     * @ORM\Column(name="store_id", type="string")
     */
    private $store;


    private $createdAt;
    private $updateAt;

    /**
     * @return mixed
     */
    public function getCatalogName()
    {
        return $this->catalogName;
    }

}
