<?php

namespace ERPBundle\Commands;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Entity\ShopifyOrderEntity;
use ERPBundle\Services\Client\ShopifyApiClientWrapper;
use ERPBundle\Services\ProductCatalogService;
use ERPBundle\Services\ShopifyStoreService;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CollectShipments
 * @package ERPBundle\Commands
 */
class CollectShipments extends Command
{

    private $shopifyStoreService;
    private $producer;
    private $shopifyClient;

    /**
     * @param ShopifyStoreService $shopifyStoreService
     * @param Producer $producer
     * @param ShopifyApiClientWrapper $shopifyClient
     */
    public function __construct(
        ShopifyStoreService $shopifyStoreService,
        ShopifyApiClientWrapper $shopifyClient,
        Producer $producer
    )
    {
        $this->shopifyStoreService = $shopifyStoreService;
        $this->producer = $producer;
        $this->shopifyClient = $shopifyClient;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('erp:shipment:update')
            ->setDescription('Update a store catalog')
            ->addArgument(
                'store',
                InputArgument::REQUIRED,
                'What store do you want to collect the shipment from?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = $input->getArgument('store');

        $store = $this->shopifyStoreService->getStore($store);

        $orders = $this->shopifyClient->getOrders($store);

        if(empty($orders)) {
            return $output->writeln(sprintf('No orders to be processed'));
        }

        /** @var ShopifyOrderEntity $order */
        foreach($orders as $order) {

            $orderMetaFields = $this->shopifyClient->getOrderMetaData($store, $order);

            if ($orderMetaFields->getErpOrderId() == 0) {
                continue;
            }

            $msg = json_encode(['payload' => ['erpOrderId' => $orderMetaFields->getErpOrderId(), 'storeId' => $store->getStoreId(), 'shopifyOrderId' => $order->getId()]]);

            $this->producer->setContentType('application/json')->publish($msg);

        }

    }
}
