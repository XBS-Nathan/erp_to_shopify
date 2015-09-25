<?php

namespace ERPBundle\Factory\Client;

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
    public function createClient(OptionsResolver $optionsResolver)
    {
        $baseUrl = $optionsResolver->offsetGet('base_url');
        $accessToken = $optionsResolver->offsetGet('access_token');

        $client = Shopify::settings(array(
            "shopUrl" => $baseUrl,
            "X-Shopify-Access-Token" => $accessToken
        ));

        return $client;
    }
}
