<?php

namespace ERPBundle\Entity;

/**
 * Class ShopifyProductEntity
 * @package ERPBundle\Entity
 */
class ShopifyProductEntity
{
    private $id;
    private $createdAt;
    private $updatedAt;
    private $variantId;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getVariantId()
    {
        return $this->variantId;
    }

    /**
     * @param mixed $variantId
     */
    public function setVariantId($variantId)
    {
        $this->variantId = $variantId;
    }


    /**
     * @param $product
     * @return ShopifyProductEntity
     */
    public static function createFromResponse($product)
    {
        $self = new self();
        $self->setId($product['product']['id']);
        $self->setVariantId($product['product']['variants'][0]['id']);
        $self->setCreatedAt(new \DateTime());
        $self->setUpdatedAt(new \DateTime());

        return $self;
    }
}
