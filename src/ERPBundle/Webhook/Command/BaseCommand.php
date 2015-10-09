<?php

namespace ERPBundle\Webhook\Command;

use ERPBundle\Document\Event;
use ERPBundle\Entity\StoreEntity;

/**
 * Class BaseCommand
 * @package ERPBundle\Webhook\Command
 */
abstract class BaseCommand
{
    protected $event;
    protected $store;

    /**
     * @param Event $event
     * @param StoreEntity $store
     */
    public function __construct(Event $event, StoreEntity $store)
    {
        $this->event = $event;
        $this->store = $store;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return StoreEntity
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param $dateUnixTimestamp
     * @return \DateTime
     */
    protected function getDateObject($dateUnixTimestamp)
    {
        return  \DateTime::createFromFormat('U', $dateUnixTimestamp);
    }
}