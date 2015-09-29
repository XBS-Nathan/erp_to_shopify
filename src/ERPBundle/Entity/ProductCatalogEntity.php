<?php

namespace ERPBundle\Entity;

/**
 * Class ProductCatalogEntity
 * @package ERPBundle\Services
 */
class ProductCatalogEntity
{

    private $catalog;
    private $products;


    public function setCatalog($catalog)
    {
        $this->catalog = $catalog;
    }

    public function getCatalog()
    {
        return $this->catalog;
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function setProducts(array $products)
    {
        $this->products = $products;
    }

    public static function createFromXMLResponse(\SimpleXMLElement $response)
    {
        $self = new self();

        $catalog = ($response->attributes()->PriceListCode ? (string) $response->attributes()->PriceListCode : null);

        if(is_null($catalog))
        {
            throw new \InvalidArgumentException('Invalid Catalog received from ERP system');
        }

        $self->setCatalog($catalog);

        $products = [];

        foreach($response->Category->Product as $product)
        {
            $products[] = ErpProductEntity::createFromCatalogXmlResponse($product);
        }

        if(empty($products))
        {
            throw new \InvalidArgumentException('No products to import');
        }

        $self->setProducts($products);

        return $self;
    }
}
