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
     * @MongoDB\ReferenceOne(targetDocument="BBBLoadBalancer\AdminBundle\Document\Server", simple=true)
     * @Assert\NotBlank(message = "No server given")
     */
    protected $server;
}
