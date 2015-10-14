<?php

namespace ERPBundle\Webhook\Factory;

use ERPBundle\Document\Event;
use ERPBundle\Entity\StoreEntity;
use ERPBundle\Webhook\Command\CreateOrderCommand;
use ERPBundle\Webhook\Interfaces\CommandFactoryInterface;
use ERPBundle\Webhook\Interfaces\EventInterface;

/**
 * Class ShopifyCommandFactory
 * @package ERPBundle\Webhook\Factory
 */
class ShopifyCommandFactory implements CommandFactoryInterface
{
    /**
     * @param Event $event
     * @return CreateOrderCommand
     */
    public function create(Event $event, StoreEntity $store)
    {
        $eventName = $event->getName();

        switch ($eventName) {
            case EventInterface::NAME_ORDER_CREATED:
                $command = new CreateOrderCommand($event, $store);
                break;
            default:
                throw new \RuntimeException("ShopifyCommandFactory doesn't know how to create a command for event: " . $eventName);
        }

        return $command;
    }
}
