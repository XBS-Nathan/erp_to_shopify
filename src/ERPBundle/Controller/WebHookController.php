<?php

namespace ERPBundle\Controller;

use ERPBundle\Document\Event;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\EventAlreadyProcessed;
use ERPBundle\Exception\InvalidShopifyHmac;
use ERPBundle\Repository\EventRepository;
use ERPBundle\Services\ShopifyStoreService;
use ERPBundle\Webhook\Factory\HandlerFactory;
use ERPBundle\Webhook\Interfaces\CommandFactoryInterface;
use ERPBundle\Webhook\ShopifyEventRetriever;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class WebHookController
 * @package ERPBundle\Consumer
 */
class WebHookController
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var HandlerFactory
     */
    protected $handlerFactory;

    /**
     * @var CommandFactoryInterface
     */
    protected $commandFactory;

    /**
     * @var ShopifyEventRetriever
     */
    protected $shopifyEventRetriever;

    /**
     * @var ShopifyStoreService
     */
    protected $storeService;

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param LoggerInterface $logger
     * @param ShopifyEventRetriever $shopifyEventRetriever
     * @param HandlerFactory $handlerFactory
     * @param CommandFactoryInterface $commandFactory
     * @param ShopifyStoreService $storeService
     * @param EventRepository $eventRepository
     */
    public function __construct(
        LoggerInterface $logger,
        ShopifyEventRetriever $shopifyEventRetriever,
        HandlerFactory $handlerFactory,
        CommandFactoryInterface $commandFactory,
        ShopifyStoreService $storeService,
        EventRepository $eventRepository
    ) {
        $this->logger = $logger;
        $this->handlerFactory = $handlerFactory;
        $this->commandFactory = $commandFactory;
        $this->shopifyEventRetriever = $shopifyEventRetriever;
        $this->storeService = $storeService;
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param Request $request
     *
     * @Rest\View(statusCode=204)
     * @Rest\Post("/webhook", defaults={"_format": "json"})
     *
     * @throws ResourceConflictException
     * @throws \Exception
     * @return null
     */
    public function handleWebHookAction(Request $request)
    {
        $this->logger->info($request->getContent());

        try {

            $store = $this->storeService->getStoreByRequest($request);
            $eventId = $this->shopifyEventRetriever->verifyWebhookRequest($request, $store);

            $event = $this->shopifyEventRetriever->retrieve($eventId);

            if($event) {
                throw new EventAlreadyProcessed(sprintf('Event Id %s has already been processed', $eventId));
            }

            //Save the event so we don't process this again
            $event = Event::createFromRequest($request, $eventId);
            $this->eventRepository->save($event);

            $cmd = $this->commandFactory->create($event);
            $handler = $this->handlerFactory->create($event);

            $handler->execute($cmd);

            $event->updateStatus(Event::STATUS_PROCESSED);
            $this->logger->alert(sprintf('Completed Processing event id %s', $eventId));
        }catch(\Exception $e) {

            $event->updateStatus(Event::STATUS_FAILED);
            $this->logger->alert($e->getMessage());

        }finally{

            $this->eventRepository->update($event);

            return true;
        }

    }


}

