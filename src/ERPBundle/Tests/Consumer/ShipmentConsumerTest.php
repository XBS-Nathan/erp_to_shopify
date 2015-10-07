<?php

namespace ERPBundle\Tests\Consumer;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\SkuToProductEntity;
use OldSound\RabbitMqBundle\Command\ConsumerCommand;


class ShipmentConsumerTest extends BaseWebTestCase
{
    protected $mock;

    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        $this->mock = $this->mockShopifyApiClient(self::$kernel);

        //empty the queue
        $this->cleanRabbitConsumerQueue('shipment', self::$kernel);

        $this->executeAppCommand(self::$kernel, new DropSchemaDoctrineCommand(), "doctrine:schema:drop", ["--force" => true]);
        $this->executeAppCommand(self::$kernel, new CreateSchemaDoctrineCommand(), "doctrine:schema:create");
        $this->executeAppCommand(self::$kernel, new LoadDataFixturesDoctrineCommand(), "doctrine:fixtures:load",
            ["--fixtures" => __DIR__ . "/../../Resources/DataFixtures", "--append" => true]);

    }

    public function testGetShipment()
    {
          $this->addMessageToExchange(self::$kernel, [
            'id'      => 'MONGO-ID',
            'payload' => [
                'erpOrderId'   => '12345',
                'shopifyOrderId' => '6789',
                'storeId'   => 1
            ]
        ]);

        $this->setGuzzleMockedResponses(
            'erp',
            self::$kernel,
            array(
                'order',
                'order.shipment',
            )
        );

        $historyErp = $this->getGuzzleHistory('erp', self::$kernel);

        $this->mockShopifyClientResponses(
            [
                'order',
            ]);

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'shipment', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);

        $this->assertRegExp('//', $responseText);

        $catalogRepository = $this->em->getRepository('\ERPBundle\Entity\CatalogEntity');

        /** @var CatalogEntity $catalog */
        $catalog = $catalogRepository->findOneByCatalogName('CSGMKT');

        $this->assertInstanceOf('\ERPBundle\Entity\CatalogEntity', $catalog, 'Cannot find catalog within database');
        $this->assertEquals('1063001331', $catalog->getShopifyCollectionId());

        $skuToProductRepo = $this->em->getRepository('\ERPBundle\Entity\SkuToProductEntity');

        $products = $skuToProductRepo->findByStoreId(1);

        $this->assertCount(2, $products);

        /** @var SkuToProductEntity $productOne */
        $productOne = $products[0];
        $this->assertEquals('CSG-1050CANTF', $productOne->getSku());
        $this->assertEquals('632910392', $productOne->getShopifyProductId());
        $this->assertEquals('808950810', $productOne->getVariantId());

        /** @var SkuToProductEntity $productOne */
        $productOne = $products[1];
        $this->assertEquals('CSG-1234', $productOne->getSku());
        $this->assertEquals('632910395', $productOne->getShopifyProductId());
        $this->assertEquals('808950815', $productOne->getVariantId());
    }

    public function tearDown()
    {
        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

}
