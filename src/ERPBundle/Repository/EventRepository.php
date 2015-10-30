<?php

namespace ERPBundle\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use ERPBundle\Document\Event;

/**
 * Class EventRepository
 * @package ERPBundle\Repository
 */
class EventRepository extends DocumentRepository
{

    /**
     * @param Event $event
     */
    public function save(Event $event) {
        $this->dm->persist($event);
        $this->dm->flush();
    }

    /**
     * @param Event $event
     */
    public function update(Event $event) {
        $this->dm->flush($event);
    }

}
