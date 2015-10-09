<?php

namespace ERPBundle\Webhook\Command;

use ERPBundle\Document\Event;
use ERPBundle\Entity\ShopifyOrderEntity;
use ERPBundle\Entity\StoreEntity;
use Symfony\Component\HttpKernel\HttpCache\Store;

/**
 * Class CreateOrderCommand
 * @package ERPBundle\Webhook\Command
 */
class CreateOrderCommand extends BaseCommand
{
    /**
     * @var ShopifyOrderEntity
     */
    protected $order;

    /**
     * @param Event $event
     * @param StoreEntity $store
     */
    public function __construct(Event $event, StoreEntity $store)
    {
        parent::__construct($event, $store);

        $this->order = ShopifyOrderEntity::createFromResponse($event->getPayload());
    }

    /**
     * @return ShopifyOrderEntity
     */
    public function getOrder()
    {
        return $this->order;
    }

}
