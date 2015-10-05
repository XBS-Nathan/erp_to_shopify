<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\StoreEntity;
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
     * @param StoreEntity $store
     * @param $catalog
     * @param bool|false $extra
     * @return ProductCatalogEntity
     */
    public function getProducts(StoreEntity $store, CatalogEntity $catalog, $extra = false)
    {
        $request = $this->client->createRequest('GET', sprintf('%s/catalogs/%s', $store->getErpUrl(), $catalog->getCatalogName()),
            [
                'auth' => [$store->getErpUsername(), $store->getErpPassword()]
            ]
        );

        $response = $this->sendRequest($request)->xml();

        $productCatalog = ProductCatalogEntity::createFromXMLResponse($response);

        if($extra) {
            foreach ($productCatalog->getProducts() as $product) {
                $this->getProductExtraInformation($store, $catalog, $product);
            }
        }

        return $productCatalog;
    }

    /**
     * @param StoreEntity $store
     * @param CatalogEntity $catalog
     * @param ErpProductEntity $product
     */
    public function getProductExtraInformation(StoreEntity $store, CatalogEntity $catalog, ErpProductEntity $product)
    {
        $request = $this->client->createRequest(
            'GET',
            sprintf('%s/catalogs/%s/%s/all', $store->getErpUrl(), $catalog->getCatalogName(), $product->getSku()),
            [
                'auth' => [$store->getErpUsername(), $store->getErpPassword()]
            ]
        );

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
