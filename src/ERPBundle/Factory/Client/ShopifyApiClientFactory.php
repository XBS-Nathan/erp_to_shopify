<?php

namespace ERPBundle\Factory\Client;

use ERPBundle\Entity\StoreEntity;
use ERPBundle\Options\ShopifyOptions;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Shopify\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractClientFactory
 * @package ERPBundle\Services\Client
 */
class ShopifyApiClientFactory
{

    /**
     * @var Client
     */
    protected $client;

    private $logPath;

    private $logLevel;

    public function __construct($logPath, $logLevel = Logger::WARNING)
    {
        $this->logPath = $logPath;
        $this->logLevel = $logLevel;
    }

    /**
     * @param StoreEntity $store
     * @return Client
     */
    public function createClient(StoreEntity $store)
    {
         $client = new Client(array(
            "shopUrl" => $store->getShopifyStoreUrl(),
            "X-Shopify-Access-Token" => $store->getShopifyAccessToken()
        ));

        $loggerName = sprintf('shopify_%s', str_replace(' ', '_', strtolower($store->getStoreLabel())));
        $loggerPath  = sprintf('%s/%s.log', $this->logPath, $loggerName);

        $log = new Logger($loggerName);
        $log->pushHandler(new StreamHandler($loggerPath, $this->logLevel));

        $subscriber = new LogSubscriber($log);
        $client->getEmitter()->attach($subscriber);

        return $client;
    }
}
