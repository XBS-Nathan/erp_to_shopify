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
    private $sync_products;

    public function getStoreId()
    {
        return $this->storeId;
    }

    public function getStoreLabel()
    {
        return $this->storeLabel;
    }
}
