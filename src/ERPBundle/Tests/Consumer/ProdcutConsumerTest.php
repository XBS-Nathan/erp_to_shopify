<?php

namespace ERPBundle\Tests\Consumer;

use OldSound\RabbitMqBundle\Command\ConsumerCommand;

class ProductConsumerTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->initKernel();
        $this->initEntityManager(self::$kernel);

        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

    public function testCreateProducts()
    {
          $this->addMessageToExchange(self::$kernel, [
            'id'      => 'MONGO-ID',
            'payload' => [
                'projectId'      => 'blu-N64567',
                'expertUserId'   => 'U-TESTEXPERT',
                'customerUserId' => 'U-TESTCUSTOMER',
            ]
        ]);

        $this->setGuzzleMockedResponses(
            'erp',
            self::$kernel,
            array(
                'catalog.products'
            )
        );
        $historyErp = $this->getGuzzleHistory('erp', self::$kernel);

        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'products', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);
var_dump($responseText);
        $this->assertRegExp('//', $responseText);

    }

}
