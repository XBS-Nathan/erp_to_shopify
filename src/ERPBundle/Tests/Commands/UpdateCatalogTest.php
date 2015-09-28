<?php

namespace ERPBundle\Tests\Consumer;

use OldSound\RabbitMqBundle\Command\ConsumerCommand;

class UpdateCatalogTest extends BaseWebTestCase
{
    public function setUp()
    {
        $this->initKernel(['environment' => 'test']);
        $this->initEntityManager(self::$kernel);

        //empty the queue
        $this->cleanRabbitConsumerQueue('product', self::$kernel);
    }

    public function testCreateRabbitMqJob()
    {
        $commandName = 'rabbitmq:consumer';
        $command = new ConsumerCommand();
        $options = ['name' => 'product', '-m' => '1'];

        $responseText = $this->executeAppCommand(self::$kernel, $command, $commandName, $options);

        $this->assertRegExp('//', $responseText);

    }

}
