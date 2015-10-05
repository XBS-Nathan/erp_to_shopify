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
    private $fullDescription;

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
     * @param \DateTimeInterface $lastUpdated
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

    /**
     * @return mixed
     */
    public function getFullDesription()
    {
        return $this->fullDescription;
    }

    /**
     * @param mixed $fullDescription
     */
    public function setFullDesription($fullDescription)
    {
        $this->fullDescription = $fullDescription;
    }

    /**
     * @param \SimpleXMLElement $product
     * @return ErpProductEntity
     */
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

        foreach($product->SupplementalData as $data) {
            foreach($data->attributes() as $k => $v) {
                switch((string) $v) {
                    case 'webcategory':
                        $self->setCategory((string) $data);
                        break;
                }
            }
        }

        $self->setPrice((string) $product->Pricing->UnitPrice);
        $self->setQty((int) (string) $product->Availability->QtyAvailable);


        return $self;
    }

    /**
     * @param ErpProductEntity $product
     * @param \SimpleXMLElement $data
     */
    public static function updateProduct(ErpProductEntity $product, \SimpleXMLElement $data)
    {
        if($product->getSku() != $data->attributes()->ItemNo) {
            throw new \InvalidArgumentException(sprintf('product %s is not the same as the response product %s', $product->getSku(), $data->attributes()->ItemNo));
        }

        $product->setInventoryPolicy(($data->ItemSpecialString && $data->ItemSpecialString == "1" ? 'continue' : 'deny'));
        $product->setStockManagement(($data->ItemSpecialString && $data->ItemSpecialString == "1" ? '' : 'shopify'));

        if(isset($data->ImageSource)) {
            $product->setImage((string)$data->ImageSource);
        }

        $fullDescription = '';
        foreach($data->TextDescription as $line)
        {
            $fullDescription .= $line .' ';
        }

        $product->setFullDesription($fullDescription);
     }
}
