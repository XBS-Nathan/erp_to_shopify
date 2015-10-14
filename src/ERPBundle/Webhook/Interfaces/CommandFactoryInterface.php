<?php

namespace ERPBundle\Webhook\Interfaces;

use ERPBundle\Document\Event;
use ERPBundle\Entity\StoreEntity;

interface CommandFactoryInterface
{
    public function create(Event $event, StoreEntity $store);
}
