<?php

namespace ERPBundle\Tests\Consumer;

use OldSound\RabbitMqBundle\Command\ConsumerCommand;

class ProductConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

    public function testCreateProducts()
    {
          $this->addMessageToExchange(self::$kernel, [
            'id'      => 'MONGO-ID',
            'payload' => [
                'catalog'      => 'erp',
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

        $this->setGuzzleMockedResponses(
            'shopify',
            self::$kernel,
            array(
                'create.product',
                'create.product2'
            )
        );
        $historyShopify = $this->getGuzzleHistory('shopify', self::$kernel);

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'product', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);

        $this->assertRegExp('//', $responseText);

    }

}
