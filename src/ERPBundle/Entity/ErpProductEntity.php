<?php

namespace ERPBundle\Entity;

/**
 * Class ErpProductEntity
 * @package ERPBundle\Entity
 */
class ErpProductEntity
{

    private $sku;
    private $title;
    private $category;
    private $description;
    private $image;

    private $price;
    private $qty;

    private $stockManagement;
    private $inventoryPolicy;

    private $lastUpdated;

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * @param mixed $lastUpdated
     */
    public function setLastUpdated(\DateTimeInterface $lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * @return mixed
     */
    public function getInventoryPolicy()
    {
        return $this->inventoryPolicy;
    }

    /**
     * @param mixed $inventoryPolicy
     */
    public function setInventoryPolicy($inventoryPolicy)
    {
        $this->inventoryPolicy = $inventoryPolicy;
    }

    /**
     * @return mixed
     */
    public function getStockManagement()
    {
        return $this->stockManagement;
    }

    /**
     * @param mixed $stockManagement
     */
    public function setStockManagement($stockManagement)
    {
        $this->stockManagement = $stockManagement;
    }



    public static function createFromCatalogXmlResponse(\SimpleXMLElement $product)
    {
        $self = new self();

        $sku = (isset($product->attributes()->ItemNo) ? (string) $product->attributes()->ItemNo : null);

        if(!$sku)
        {
            throw new \InvalidArgumentException('Invalid product downloaded from catalog');
        }

        $self->setSku($sku);
        $self->setTitle((string) $product->Description);
        $self->setLastUpdated(new \DateTime((string) $product->attributes()->LastUpdated));
        $self->setDescription((string) $product->Description);
        $self->setImage((string) $product->SupplementalData[0]);
        $self->setCategory((string) $product->SupplementalData[2]);
        $self->setPrice((string) $product->Pricing->UnitPrice);
        $self->setQty((string) $product->Availability->QtyAvailable);


        return $self;
    }

    public static function updateProduct(ErpProductEntity $product, \SimpleXMLElement $data)
    {
//        $self->setInventoryPolicy(($product->product['StockManagement'] ? 'continue' : 'deny'));
//        $self->setStockManagement(($product->product['StockManagement'] ? '' : 'shopify'));
//        $self->setFullDescription();

//                'body_html' => $this->product['Body'],
        return $product;
    }
}
