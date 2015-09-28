<?php

namespace ERPBundle\Factory\Client;

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

    /**
     * @param ShopifyOptions $optionsResolver
     * @return Client
     */
    public static function createClient(ShopifyOptions $optionsResolver)
    {
        $config = $optionsResolver->getConfig();

        $client = new Client(array(
            "shopUrl" => $config['base_url'],
            "X-Shopify-Access-Token" => $config['token']
        ));

        $log = new Logger('shopify_store_name');
        $log->pushHandler(new StreamHandler('/opt/erp/app/logs/shopify.log', Logger::WARNING));

        $subscriber = new LogSubscriber($log);
        $client->getEmitter()->attach($subscriber);

        return $client;
    }
}
