<?php

namespace ERPBundle\Resources\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\StoreEntity;

class LoadCatalogEntity implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $catalogCSGMKT = new CatalogEntity();
        $catalogCSGMKT->setCatalogName('CSGMKT');
        $catalogCSGMKT->setStoreId(1);
        $catalogCSGMKT->setShopifyCollectionId(0);

        $manager->persist($catalogCSGMKT);



        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // the order in which fixtures will be loaded
    }
}
