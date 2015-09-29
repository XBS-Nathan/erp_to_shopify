<?php

namespace ERPBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\SkuToProductEntity;

/**
 * Class CatalogRepository
 * @package ERPBundle\Repository
 */
class CatalogRepository extends EntityRepository
{

    /**
     * @param CatalogEntity $catalogEntity
     */
    public function save(CatalogEntity $catalogEntity)
    {
        $this->_em->persist($catalogEntity);
        $this->_em->flush();
    }
}
