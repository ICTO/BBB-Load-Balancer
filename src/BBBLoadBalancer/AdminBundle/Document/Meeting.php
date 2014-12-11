<?php

namespace BBBLoadBalancer\AdminBundle\Document;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\Document(collection="Meeting")
 */
class Meeting
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     * @Assert\NotBlank(message = "No meeting ID")
     */
    protected $meetingId;

    /**
     * @MongoDB\ReferenceOne(targetDocument="BBBLoadBalancer\AdminBundle\Document\Server", simple=true)
     * @Assert\NotBlank(message = "No server given")
     */
    protected $server;

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set meetingId
     *
     * @param string $meetingId
     * @return self
     */
    public function setMeetingId($meetingId)
    {
        $this->meetingId = $meetingId;
        return $this;
    }

    /**
     * Get meetingId
     *
     * @return string $meetingId
     */
    public function getMeetingId()
    {
        return $this->meetingId;
    }

    /**
     * Set server
     *
     * @param BBBLoadBalancer\AdminBundle\Document\Server $server
     * @return self
     */
    public function setServer(\BBBLoadBalancer\AdminBundle\Document\Server $server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * Get server
     *
     * @return BBBLoadBalancer\AdminBundle\Document\Server $server
     */
    public function getServer()
    {
        return $this->server;
    }
}
