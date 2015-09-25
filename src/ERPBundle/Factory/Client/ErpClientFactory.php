<?php

namespace ERPBundle\Factory\Client;

use GuzzleHttp\Client;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractClientFactory
 * @package ERPBundle\Services\Client
 */
class ErpClientFactory
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

        $config = [
            'base_url' => $baseUrl
        ];

        $this->client = new Client($config);

        return $this->client;
    }
}
