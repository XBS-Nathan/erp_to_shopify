<?php

namespace ERPBundle\Factory\Client;

use ERPBundle\Options\ShopifyOptions;
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
     * @param OptionsResolver $optionsResolver
     * @return Client
     */
    public static function createClient(ShopifyOptions $optionsResolver)
    {
        $config = $optionsResolver->getConfig();

        $client = new Client(array(
            "shopUrl" => $config['base_url'],
            "X-Shopify-Access-Token" => $config['token']
        ));

        return $client;
    }
}
