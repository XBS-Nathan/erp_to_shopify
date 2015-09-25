<?php

namespace ERPBundle\Services\Client;


use ERPBundle\Services\ProductCatalogEntity;
use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;

/**
 * Class ErpClient
 * @package ERPBundle\Services\Client
 */
class ErpClient
{

    /**
     * @var Client
     */
    protected $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $catalog
     */
    public function getProducts($catalog)
    {
        $request = $this->client->createRequest('GET', sprintf('catalogs/%s', $catalog));

        $response = $this->sendRequest($request)->xml();

        $productCatalog = ProductCatalogEntity::createFromResponse($response);

        return $productCatalog;
    }

    /**
     * @param RequestInterface $request
     */
    public function sendRequest(RequestInterface $request)
    {
        try {
            $this->client->send($request);
        } catch(RequestException $e ) {
            //Catch the error and handle it accordingly.
        }
    }

}
