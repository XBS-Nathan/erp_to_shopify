<?php

namespace ERPBundle\Resources\DataFixtures;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use ERPBundle\Entity\StoreEntity;

class LoadStoreEntity implements FixtureInterface
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $store = new StoreEntity();
        $store->setStoreLabel('My Test Store');
        $store->setSyncProducts(1);
        $store->setShopifyAccessToken('06ccecd6867cc78d4423e4bb8058a984');
        $store->setShopifyStoreUrl('erpapitest.myshopify.com');
        $store->setErpUrl('http://robots.lapineinc.com');
        $store->setErpUsername('CSGTEST');
        $store->setErpPassword('yG9uFFrLeHZ56LL4');
        $store->setShopifySecretToken('MySecretToken');
        $store->setShopifyHandlingFeeProductId(1);

        $manager->persist($store);
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
