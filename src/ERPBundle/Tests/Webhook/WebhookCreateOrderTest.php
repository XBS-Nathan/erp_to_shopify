<?php

namespace ERPBundle\Tests\Consumer;

use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\DropSchemaDoctrineCommand;
use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\SkuToProductEntity;
use OldSound\RabbitMqBundle\Command\ConsumerCommand;


class WebhookCreateOrderTest extends BaseWebTestCase
{
    protected $mock;

    protected $client;

    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        $this->mock = $this->mockShopifyApiClient(self::$kernel);

        $this->executeAppCommand(self::$kernel, new DropSchemaDoctrineCommand(), "doctrine:schema:drop",
            ["--force" => true]);
        $this->executeAppCommand(self::$kernel, new CreateSchemaDoctrineCommand(), "doctrine:schema:create");
        $this->executeAppCommand(self::$kernel, new LoadDataFixturesDoctrineCommand(), "doctrine:fixtures:load",
            ["--fixtures" => __DIR__ . "/../../Resources/DataFixtures", "--append" => true]);

        $this->client = self::createClient();

    }

    public function testCreateOrder()
    {

        $this->setGuzzleMockedResponses(
            'erp',
            self::$kernel,
            array(
                'order.created'
            )
        );

        $historyErp = $this->getGuzzleHistory('erp', self::$kernel);

        $this->mockShopifyClientResponses(
            [
                'order',
                'update.fulfillment'
            ]);

        $headers = [
            'HTTP_X-Shopify-Shop-Domain' => 'erpapitest.myshopify.com',
            'HTTP_X_SHOPIFY_HMAC_SHA256' => 'EPNrGEidakGyssezRCThra+cLRtkuAAXgsVB88i+xpo=',
            'HTTP_X-Shopify-Topic' => 'orders/create'
        ];


        $path = __DIR__ . '/../../Resources/test_stubs/webhooks/';
        $body = json_decode(file_get_contents($path . 'order.json'), true);

        $this->client->request(
            'POST',
            '/webhook',
            $body,
            [],
            $headers
        );

        $response = $this->client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());

    }

    public function testNoShopifyStoreInHeader()
    {

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
                'update.fulfillment'
            ]);


        $this->client->request(
            'POST',
            '/webhook'
        );

        $response = $this->client->getResponse();

        $this->assertEquals(400, $response->getStatusCode());

    }

    public function tearDown()
    {
        $eventOne = $this->dm->getRepository('ERPBundle:Event')->findOneByEventId("EPNrGEidakGyssezRCThra+cLRtkuAAXgsVB88i+xpo=");
        if ($eventOne) {
            $this->dm->remove($eventOne);
        }

        $this->dm->flush();
    }
}

