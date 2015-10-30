<?php

namespace ERPBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\SkuToProductEntity;
use ERPBundle\Entity\StoreEntity;

/**
 * Class StoreRepository
 * @package ERPBundle\Repository
 */
class StoreRepository extends EntityRepository
{
    /**
     * @param StoreEntity $store
     */
    public function save(StoreEntity $store)
    {
        $this->_em->persist($store);
        $this->_em->flush();
    }
}
