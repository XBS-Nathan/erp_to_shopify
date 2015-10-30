<?php

namespace ERPBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\SkuToProductEntity;

/**
 * Class SkuToShopifyProductRepository
 * @package ERPBundle\Repository
 */
class SkuToShopifyProductRepository extends EntityRepository
{

    /**
     * @param SkuToProductEntity $erpProduct
     */
    public function save(SkuToProductEntity $erpProduct)
    {
        $this->_em->persist($erpProduct);
        $this->_em->flush();
    }

    /**
     * @param SkuToProductEntity $skuToProductEntity
     */
    public function update(SkuToProductEntity $skuToProductEntity)
    {
        $this->_em->persist($skuToProductEntity);
        $this->_em->flush();
        $this->_em->refresh($skuToProductEntity);
    }

    /**
     * @param SkuToProductEntity $skuToProductEntity
     */
    public function remove(SkuToProductEntity $skuToProductEntity)
    {
        $this->_em->remove($skuToProductEntity);
    }

    public function flush()
    {
        $this->_em->flush();
    }

}
