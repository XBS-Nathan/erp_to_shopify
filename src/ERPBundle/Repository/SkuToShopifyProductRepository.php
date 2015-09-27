<?php

namespace ERPBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ERPBundle\Entity\ErpProductEntity;

class SkuToShopifyProductRepository extends EntityRepository
{

    public function save(ErpProductEntity $erpProduct)
    {
        $this->_em->persist($erpProduct);
        $this->_em->flush();
    }

}
