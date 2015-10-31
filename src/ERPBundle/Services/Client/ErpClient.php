<?php

namespace ERPBundle\Services\Client;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ErpOrderEntity;
use ERPBundle\Entity\ErpProductEntity;
use ERPBundle\Entity\ErpShipmentEntity;
use ERPBundle\Entity\ProductCatalogEntity;
use ERPBundle\Entity\ShopifyOrderEntity;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\ErpOrderNotFound;
use ERPBundle\Exception\ErpShipmentNotFound;
use ERPBundle\Exception\OrderNotFound;
use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
     * @param string $logPath
     */
    public function __construct(Client $client, $logPath)
    {
        $this->client = $client;
        $loggerPath  = sprintf('%s/%s.log', $logPath, 'erp_client_logger');
        $log = new Logger('erp_client_logger');
        $log->pushHandler(new StreamHandler($loggerPath, Logger::DEBUG));
        $subscriber = new LogSubscriber($log);
        $this->client->getEmitter()->attach($subscriber);

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
     * @param StoreEntity $store
     * @param $orderId
     * @return ErpOrderEntity
     * @throws ErpOrderNotFound
     */
    public function getOrder(StoreEntity $store, $orderId)
    {
        $request = $this->client->createRequest('GET', sprintf('%s/orders/%s', $store->getErpUrl(), $orderId),
            [
                'auth' => [$store->getErpUsername(), $store->getErpPassword()]
            ]
        );

        try {
            $response = $this->sendRequest($request)->xml();
            $order = ErpOrderEntity::createFromOrderXMLObject($response, $orderId);
        }catch (\Exception $e) {
            throw new ErpOrderNotFound(sprint('Order Id %s is not yet ready'));
        }

        return $order;
    }

    /**
     * @param StoreEntity $store
     * @param ShopifyOrderEntity $orderEntity
     * @return ErpOrderEntity
     */
    public function createOrder(StoreEntity $store, ShopifyOrderEntity $orderEntity)
    {
        $xmlObject = ShopifyOrderEntity::convertToXmlForErp($orderEntity, $store);

        $request = $this->client->createRequest('POST', sprintf('%s/orders/', $store->getErpUrl()),
            [
                'auth' => [$store->getErpUsername(), $store->getErpPassword()],
                'body' => $xmlObject->asXML()
            ]
        );

        $response = $this->sendRequest($request)->xml();

        return ErpOrderEntity::createFromShopifyOrder($response, $orderEntity);
    }

    /**
     * @param StoreEntity $store
     * @param ErpOrderEntity $erpOrder
     * @return ErpShipmentEntity
     * @throws ErpShipmentNotFound
     */
    public function getShipment(StoreEntity $store, ErpOrderEntity $erpOrder)
    {
        $request = $this->client->createRequest('GET', sprintf('%s/shipments/?order=%s', $store->getErpUrl(), $erpOrder->getOrderId()),
            [
                'auth' => [$store->getErpUsername(), $store->getErpPassword()]
            ]
        );

        try {
            $response = $this->sendRequest($request)->xml();
        } catch (\Exception $e) {
            throw new ErpShipmentNotFound(sprintf('Not shipment can be found with the order id %s', $erpOrder->getOrderId()));
        }

        return ErpShipmentEntity::createFromShipmentXMLObject($response);
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
