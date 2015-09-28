<?php

namespace ERPBundle\Commands;

use ERPBundle\Entity\CatalogEntity;
use ERPBundle\Services\ProductCatalogService;
use ERPBundle\Services\ShopifyStoreService;
use OldSound\RabbitMqBundle\RabbitMq\Producer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateCatalog
 * @package ERPBundle\Commands
 */
class UpdateCatalog extends Command
{

    public function __construct(
        ShopifyStoreService $shopifyStoreService,
        Producer $producer
    )
    {
        $this->shopifyStoreService = $shopifyStoreService;
        $this->producer = $producer;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('erp:catalog:update')
            ->setDescription('Update a store catalog')
            ->addArgument(
                'store',
                InputArgument::REQUIRED,
                'What store do you want to update?'
            )
            ->addArgument(
                'catalog',
                InputArgument::OPTIONAL,
                'What catalog do you want to update?'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\ORM\NoResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = $input->getArgument('store');
        $catalog = $input->getArgument('catalog');

        if(!$catalog) {
            $catalog = CatalogEntity::$ALL;
        }

        $store = $this->shopifyStoreService->getStore($store);

        $catalogs = $this->shopifyStoreService->getCatalog($catalog, $store);

        $output->writeln(sprintf('Gathering data for store id %', $store));
        foreach($catalogs as $catalog) {

            $msg = json_encode(['payload' => ['catalog' => $catalog->getCatalogName()]]);

            $this->producer->setContentType('application/json')->publish($msg);

            $output->writeln(sprintf('Store %s has been sent to be updated for the catalog %s', $store->getStoreLabel(), $catalog->getCatalogName()));

        }

    }


}
