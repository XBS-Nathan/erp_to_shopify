<?php

namespace ERPBundle\Tests\Consumer;

use GuzzleHttp\Subscriber\Mock;
use OldSound\RabbitMqBundle\Command\ConsumerCommand;
use Shopify\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class ProductConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        $this->mockShopifyApiClient(
            self::$kernel,
            [
                'count.collection',
                'product.collection',
                'update.product',
                'update.product',
                'delete.collection',
                'create.collection',
                'update.collection'
            ]
        );

        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

    public function mockShopifyApiClient(KernelInterface $kernel, $responses)
    {
        $shopClientFactory = $this->getMockBuilder('\ERPBundle\Factory\Client\ShopifyApiClientFactory')->disableOriginalConstructor()->getMock();

        $shopifyApiClient = new Client(array(
            "shopUrl" => 'myStore',
            "X-Shopify-Access-Token" => 'MyAccessToken'
        ));

        $mock = new Mock();
        $shopifyApiClient->getEmitter()->attach($mock);

        $shopClientFactory->expects($this->any())
                            ->method('createClient')
                            ->willReturn($shopifyApiClient);

        $kernel->getContainer()->set('erp.guzzle.client.shopify', $shopClientFactory);

        $path = __DIR__.'/../../Resources/test_stubs/shopify/';
        foreach ($responses as $response) {
            $file = $path.$response.'.response';
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('Mock '.$response.' for client shopify not found at '.$file);
            }
            $mock->addResponse($file);
        }
    }

    public function testCreateProducts()
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

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'product', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);

        $this->assertRegExp('//', $responseText);

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
