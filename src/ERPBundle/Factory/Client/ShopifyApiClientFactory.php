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

        $log = new Logger('shopify_store_name');
        $log->pushHandler(new StreamHandler('/opt/erp/app/logs/shopify.log', Logger::WARNING));

        $subscriber = new LogSubscriber($log);
        $client->getEmitter()->attach($subscriber);

        return $client;
    }
}
