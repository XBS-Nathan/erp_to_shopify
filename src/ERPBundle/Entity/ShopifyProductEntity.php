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
    private $sku;
    private $qty;
    private $handle;

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
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @param mixed $sku
     */
    public function setSku($sku)
    {
        $this->sku = $sku;
    }

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * @param mixed $handle
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;
    }

    /**
     * @param $product
     * @return ShopifyProductEntity
     */
    public static function createFromResponse($product)
    {
        $self = new self();
        $self->setId($product['id']);
        $self->setVariantId($product['variants'][0]['id']);
        $self->setCreatedAt(new \DateTime());
        $self->setUpdatedAt(new \DateTime());
        $self->setSku($product['variants'][0]['sku']);
        $self->setQty($product['variants'][0]['inventory_quantity']);
        $self->setHandle($product['handle']);

        return $self;
    }

    /**
     * @param $product
     * @return ShopifyProductEntity
     */
    public static function createFromProductCreationResponse($product)
    {
        $self = new self();
        $self->setId($product['product']['id']);
        $self->setVariantId($product['product']['variants'][0]['id']);
        $self->setCreatedAt(new \DateTime());
        $self->setUpdatedAt(new \DateTime());
        $self->setSku($product['product']['variants'][0]['sku']);
        $self->setHandle($product['product']['handle']);

        return $self;
    }

    public static function createHandlingFeeProduct()
    {
        $self = new self();
        $self->handle = 'handling-fees';
    }
}
