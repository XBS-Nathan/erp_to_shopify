<?php

namespace ERPBundle\Webhook;

use ERPBundle\Entity\StoreEntity;
use ERPBundle\Exception\InvalidShopifyHmac;
use ERPBundle\Repository\EventRepository;
use ERPBundle\Webhook\Interfaces\EventInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ShopifyEventRetriever
 * @package ERPBundle\Webhook
 */
class ShopifyEventRetriever implements EventRetrieverInterface
{

    const SHOPIFY_HEADER_EVENT_NAME = 'X-Shopify-Topic';

    /**
     * @var EventRepository
     */
    protected $eventRepository;

    /**
     * @param EventRepository $eventRepository
     */
    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    /**
     * @param $eventId
     * @return object
     */
    public function retrieve($eventId)
    {
        $event = $this->eventRepository->findOneBy(['eventId' => $eventId]);

        return $event;
    }

    /**
     * @param Request $request
     * @param StoreEntity $store
     * @return string
     * @throws InvalidShopifyHmac
     */
    public function verifyWebhookRequest(Request $request, StoreEntity $store)
    {
        $hmac_header = $request->headers->get('HTTP_X_SHOPIFY_HMAC_SHA256');

        $calculated_hmac = base64_encode(hash_hmac('sha256', $request->getContent(), $store->getShopifySecretToken(), true));
        if($hmac_header != $calculated_hmac) {
            throw new InvalidShopifyHmac();
        }

        return $calculated_hmac;
    }
}
