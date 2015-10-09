<?php

namespace ERPBundle\Webhook\Interfaces;

use ERPBundle\Document\Event;

interface CommandFactoryInterface
{
    public function create(Event $event);
}
