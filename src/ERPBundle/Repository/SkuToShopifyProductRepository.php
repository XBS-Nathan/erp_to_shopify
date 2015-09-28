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

}
