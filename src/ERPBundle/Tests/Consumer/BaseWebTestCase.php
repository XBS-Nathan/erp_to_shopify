<?php

namespace ERPBundle\Tests\Consumer;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpKernel\KernelInterface;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Subscriber\History;
use Shopify\Client as ShopifyClient;

abstract class BaseWebTestCase extends SymfonyWebTestCase
{
    protected $container;

    /** @var  \Doctrine\ORM\EntityManager */
    protected $em;

    public function setUp()
    {
        $this->initEntityManager();
    }

    protected function initKernel($options = [])
    {
        $kernel = static::createKernel($options);
        $kernel->boot();

        $this->container = $kernel->getContainer();

        self::$kernel = $kernel;

        return $kernel;
    }

    protected function cleanRabbitConsumerQueue($consumer, KernelInterface $kernel) {
        $consumerName = 'old_sound_rabbit_mq.'.$consumer.'_consumer';

        /** @var  \OldSound\RabbitMqBundle\RabbitMq\Consumer $consumer */
        $producer = $kernel->getContainer()->get($consumerName);
        $producer->purge();
    }

    protected function initEntityManager($kernel = null)
    {
        if (!$kernel) {
            $kernel = $this->initKernel();
        }
        $this->em   = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        return $this->em;
    }

    /**
     * @param KernelInterface $kernel
     * @param mixed $command Object command
     * @param string $commandName name of the command ex demo:great
     * @return mixed|string
     */
    protected function executeAppCommand(KernelInterface $kernel, $command, $commandName, $options = array())
    {
        $application = new Application($kernel);

        $application->add($command);
        $command = $application->find($commandName);
        $commandTester = new CommandTester($command);

        if(method_exists($command, 'setContainer')) {
            $command->setContainer($kernel->getContainer());
        }

        $commandParameters = $options;
        $commandParameters[] = ['command' => $command->getName()];

        $commandTester->execute($commandParameters);

        return $commandTester->getDisplay();
    }

    /**
     *
     * The clientName indicate the client used, and indicate in which folder we need to search the stub
     * for examlple clientName=uas will find the file in Resource/test_stubs/uas/
     *
     * @param $name
     * @param string $clientName uas, dms, etc
     * @return string
     */
    protected function getResponseFixture($name, $clientName)
    {
        $fixturePath = __DIR__ . "/../Resources/test_stubs/".$clientName."/";

        $response = file_get_contents($fixturePath . $name . '.json');

        return $response;
    }

    /**
     * @param KernelInterface | Client $kernelOrClient
     * @param array $responses
     * @param string $serviceClient
     * @return History
     */
    protected function mockGuzzleResponses($kernelOrClient, $responses, $serviceClient)
    {
        $guzzleClient = $kernelOrClient->getContainer()->get($serviceClient);

        $mock = new Mock($responses);
        // Add the mock subscriber to the client.
        $guzzleClient->getEmitter()->attach($mock);
        $history = new History();
        // Add the history subscriber to the client.
        $guzzleClient->getEmitter()->attach($history);

        return $history;
    }

    /**
     * @param $kernel
     * @param $message
     */
    protected function addMessageToExchange($kernel, $message)
    {
        $this->producerService = $kernel->getContainer()->get(
            'old_sound_rabbit_mq.product_producer'
        );

        $msg = json_encode($message);

        $this->producerService->setContentType('application/json')->publish($msg);
    }

    /**
     * Get guzzle client
     *
     * @param  string          $clientName Client name
     * @param  KernelInterface $kernel     Kernel
     * @return GuzzleClient
     */
    public function getGuzzleClient($clientName, KernelInterface $kernel)
    {
        return $this->getService('blur_guzzle.guzzle_client.'.$clientName, $kernel);
    }

    /**
     * Get Guzzle History service
     *
     * @param  string          $clientName Client name
     * @param  KernelInterface $kernel     Kernel
     * @return GuzzleHistory
     */
    public function getGuzzleHistory($clientName, KernelInterface $kernel)
    {
        return $kernel->getContainer()->get('erp.guzzle.client.'.$clientName);
    }

    /**
     * Get Guzzle Mock service
     *
     * @param  string          $clientName Client name
     * @param  KernelInterface $kernel     Kernel
     * @return GuzzleMock
     */
    public function getGuzzleMock($clientName, KernelInterface $kernel)
    {
        $mock = new Mock();

        $client = $kernel->getContainer()->get('erp.guzzle.client.'. $clientName);
        $client->getEmitter()->attach($mock);

        return $mock;
    }

    /**
     * Set Guzzle mocked responses
     *
     * @param string          $clientName Client name
     * @param KernelInterface $kernel     Kernel
     * @param array           $responses  Array of responses
     */
    public function setGuzzleMockedResponses($clientName, KernelInterface $kernel, array $responses = array())
    {
        $mock = $this->getGuzzleMock($clientName, $kernel);
        $path = __DIR__.'/../../Resources/test_stubs/'.$clientName.'/';
        foreach ($responses as $response) {
            $file = $path.$response.'.response';
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('Mock '.$response.' for client '.$clientName.' not found at '.$file);
            }
            $mock->addResponse($file);
        }
    }

    public function mockShopifyApiClient(KernelInterface $kernel)
    {
        $shopClientFactory = $this->getMockBuilder('\ERPBundle\Factory\Client\ShopifyApiClientFactory')->disableOriginalConstructor()->getMock();

        $shopifyApiClient = new ShopifyClient(array(
            "shopUrl" => 'myStore',
            "X-Shopify-Access-Token" => 'MyAccessToken'
        ));

        $mock = new Mock();
        $shopifyApiClient->getEmitter()->attach($mock);

        $shopClientFactory->expects($this->any())
                          ->method('createClient')
                          ->willReturn($shopifyApiClient);

        $kernel->getContainer()->set('erp.guzzle.client.shopify', $shopClientFactory);

        return $mock;
    }

    public function mockShopifyClientResponses(array $responses)
    {
        $path = __DIR__.'/../../Resources/test_stubs/shopify/';

        foreach ($responses as $response) {
            $file = $path . $response . '.response';
            if (!file_exists($file)) {
                throw new \InvalidArgumentException('Mock ' . $response . ' for client shopify not found at ' . $file);
            }
            $this->mock->addResponse($file);
        }
    }


}