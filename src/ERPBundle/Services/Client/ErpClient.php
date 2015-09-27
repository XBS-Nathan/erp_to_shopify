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

    public function __construct(Client $client)
    {
        $this->client = $client;
    }


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

    public function getProductExtraInformation($catalog, ErpProductEntity $product)
    {
        $request = $this->client->createRequest('GET', sprintf('catalogs/%s/%s/all', $catalog, $product->getSku()));

        $response = $this->sendRequest($request)->xml();

        //Inject the response into the product object.
        //ErpProductEntity::updateProduct($product, $data);

        return $response;
    }

    /**
     * @param RequestInterface $request
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
