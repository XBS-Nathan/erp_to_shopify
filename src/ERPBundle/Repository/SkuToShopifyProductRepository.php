<?php

namespace ERPBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\SkuToProductEntity;

class SkuToShopifyProductRepository extends EntityRepository
{

    public function save(SkuToProductEntity $erpProduct)
    {
        $this->_em->persist($erpProduct);
        $this->_em->flush();
    }

    public function update(SkuToProductEntity $skuToProductEntity)
    {
        $this->_em->persist($skuToProductEntity);
        $this->_em->flush();
        $this->_em->refresh($skuToProductEntity);
    }

}
