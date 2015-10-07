<?php

namespace ERPBundle\Tests\Consumer;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\SkuToProductEntity;
use GuzzleHttp\Subscriber\Mock;
use OldSound\RabbitMqBundle\Command\ConsumerCommand;
use Shopify\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class ProductConsumerTest extends BaseWebTestCase
{
    protected $mock;

    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        $this->mock = $this->mockShopifyApiClient(self::$kernel);

        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);

        $this->executeAppCommand(self::$kernel, new DropSchemaDoctrineCommand(), "doctrine:schema:drop", ["--force" => true]);
        $this->executeAppCommand(self::$kernel, new CreateSchemaDoctrineCommand(), "doctrine:schema:create");
        $this->executeAppCommand(self::$kernel, new LoadDataFixturesDoctrineCommand(), "doctrine:fixtures:load",
            ["--fixtures" => __DIR__ . "/../../Resources/DataFixtures", "--append" => true]);

    }

    public function testUpdateProducts()
    {
          $this->addMessageToExchange(self::$kernel, [
            'id'      => 'MONGO-ID',
            'payload' => [
                'catalog'   => 'CSGMKT',
                'storeId'   => 1
            ]
        ]);

        $this->setGuzzleMockedResponses(
            'erp',
            self::$kernel,
            array(
                'catalog.products',
                'product.full',
                'product2.full',
            )
        );

        $historyErp = $this->getGuzzleHistory('erp', self::$kernel);

        $this->mockShopifyClientResponses(
            [
                'count.collection',
                'product.collection',
                'update.product',
                'update.product2',
                'delete.collection',
                'create.collection',
                'update.collection'
            ]);

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'product', '-m' => '1'];

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

    public function createProducts()
    {
        $this->mockShopifyClient(
            [
                'count.collection',
                'product.collection',
                'create.product',
                'create.product',
                'delete.collection',
                'create.collection',
                'update.collection'
            ]);
    }

    public function NoCatalogExists()
    {
        $this->addMessageToExchange(self::$kernel, [
            'id'      => 'MONGO-ID',
            'payload' => [
                'catalog'      => 'dontExistCatalog',
                'storeId'   => 1
            ]
        ]);

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'product', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);

        $this->assertRegExp('/No result was found for query although at least one row was expected./', $responseText);

    }

    public function tearDown()
    {
        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

}
