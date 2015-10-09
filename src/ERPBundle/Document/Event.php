<?php

namespace ERPBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Doctrine\Common\Collections\ArrayCollection;
use ERPBundle\Webhook\Interfaces\EventInterface;
use ERPBundle\Webhook\ShopifyEventRetriever;
use Symfony\Component\HttpFoundation\Request;


/**
 * @MongoDB\Document(repositoryClass="ERPBundle\Repository\EventRepository")
 */
class Event
{
    const STATUS_NEW        = "new";
    const STATUS_PROCESSED  = "processed";
    const STATUS_FAILED     = "failed";

    /**
     * @MongoDB\Id
     *
     * @var string
     */
    protected $id;

    /**
     * @MongoDB\String
     *
     * @var string
     */
    protected $name;

    /**
     * @MongoDB\String
     *
     * @var string
     */
    protected $eventId;

    /**
     * @MongoDB\String
     *
     * @var string
     */
    protected $status;

    /**
     * @MongoDB\Date
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @MongoDB\Hash
     *
     * @var array
     */
    protected $payload;

    /**
     * @var ArrayCollection
     *
     * @MongoDB\ReferenceMany(targetDocument="Log", mappedBy="event")
     */

    //prevents improper creation of the object
    private function __construct()
    {
        $this->date     = new \DateTime('now');
        $this->status   = self::STATUS_NEW;
    }

    /**
     * @param Request $request
     * @param $eventId
     * @return Event
     */
    public static function createFromRequest(Request $request, $eventId)
    {
        $eventDocument = new self();

        // Request and creation data
        $eventDocument->eventId       = $eventId;
        $eventDocument->name          = $request->headers->get(ShopifyEventRetriever::SHOPIFY_HEADER_EVENT_NAME);
        $eventDocument->payload       = $request->request->all();

        return $eventDocument;
    }

    /**
     * @param $newStatus
     */
    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return string
     */
    public function getSourceHost()
    {
        return $this->sourceHost;
    }

    /**
     * @return string
     */
    public function getSourceIp()
    {
        return $this->sourceIp;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

}
