<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
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

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param $catalog
     * @param bool|false $extra
     * @return ProductCatalogEntity
     */
    public function getProducts($catalog, $extra = false)
    {
        $request = $this->client->createRequest('GET', sprintf('catalogs/%s', $catalog));

        $response = $this->sendRequest($request)->xml();

        $productCatalog = ProductCatalogEntity::createFromXMLResponse($response);

        if($extra) {
            foreach ($productCatalog->getProducts() as $product) {
                $this->getProductExtraInformation($catalog, $product);
            }
        }

        return $productCatalog;
    }

    /**
     * @param $catalog
     * @param ErpProductEntity $product
     */
    public function getProductExtraInformation($catalog, ErpProductEntity $product)
    {
        $request = $this->client->createRequest('GET', sprintf('catalogs/%s/%s/all', $catalog, $product->getSku()));

        $response = $this->sendRequest($request)->xml();

        ErpProductEntity::updateProduct($product, $response);
    }

    /**
     * @param RequestInterface $request
     * @return \GuzzleHttp\Message\FutureResponse|\GuzzleHttp\Message\ResponseInterface|\GuzzleHttp\Ring\Future\FutureInterface|mixed|null
     */
    public function sendRequest(RequestInterface $request)
    {
        try {
            return $this->client->send($request);
        } catch(RequestException $e ) {
            //Catch the error and handle it accordingly.
        }
    }

}
