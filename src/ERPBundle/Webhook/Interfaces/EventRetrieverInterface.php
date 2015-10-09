<?php

namespace ERPBundle\Webhook\Interfaces;

use Doctrine\Common\Persistence\ObjectManager;

interface EventRetrieverInterface
{
    public function __construct(ObjectManager $dm);

    public function retrieve($eventId);
}
