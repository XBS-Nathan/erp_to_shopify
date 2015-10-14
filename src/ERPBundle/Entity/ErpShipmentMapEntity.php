<?php

namespace ERPBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="\ERPBundle\Repository\ErpShipmentMapRepository")
 * @ORM\Table(indexes={@ORM\Index(name="store_id_idx", columns={"store_id"})})
 */
class ErpShipmentMapEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="StoreEntity", inversedBy="shipmentMaps")
     * @ORM\JoinColumn(name="store_id", referencedColumnName="id", nullable=true)
     */
    private $store;

    /**
     * @ORM\Column(name="shopify_method_title", type="string")
     */
    private $shopifyMethod;

    /**
     * @ORM\Column(name="erp_method_title", type="string")
     */
    private $erpMethod;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updatedAt;

    /**
     * @return mixed
     */
    public function getStoreId()
    {
        return $this->store_id;
    }

    /**
     * @return mixed
     */
    public function getShopifyMethod()
    {
        return $this->shopifyMethod;
    }

    /**
     * @return mixed
     */
    public function getErpMethod()
    {
        return $this->erpMethod;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

}
