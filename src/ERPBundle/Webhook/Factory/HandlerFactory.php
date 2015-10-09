<?php

namespace ERPBundle\Webhook\Factory;

use ERPBundle\Document\Event;
use ERPBundle\Webhook\Handler\CreateOrderHandler;
use ERPBundle\Webhook\Interfaces\EventInterface;

/**
 * Class HandlerFactory
 * @package ERPBundle\Webhook\Factory
 */
class HandlerFactory
{

    /**
     * @var CreateOrderHandler
     */
    protected $createOrderHandler;

    /**
     * @param CreateOrderHandler $createOrderHandler
     */
    public function __construct(
        CreateOrderHandler $createOrderHandler
    ){
        $this->createOrderHandler = $createOrderHandler;
    }

    /**
     * @param Event $event
     * @return CreateOrderHandler
     */
    public function create(Event $event)
    {
        $eventName = $event->getName();

        switch ($eventName) {
            case EventInterface::NAME_ORDER_CREATED:
                $handler = $this->createOrderHandler;
                break;
            default:
                throw new \RuntimeException("HandlerFactory doesn't know how to create a handler for event: " . $eventName);
                break;
        }

        return $handler;
    }
}
