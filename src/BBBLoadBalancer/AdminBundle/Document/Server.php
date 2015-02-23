<?php

namespace BBBLoadBalancer\AdminBundle\Document;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Bundle\MongoDBBundle\Validator\Constraints\Unique as MongoDBUnique;

/**
 * @MongoDB\Document(collection="Server")
 * @MongoDBUnique(fields="url", message = "The URL field is not unique")
 */
class Server
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     * @Assert\NotBlank(message = "No name given")
     */
    protected $name;

    /**
     * @MongoDB\String
     * @Assert\NotBlank(message = "No URL given")
     * @Assert\Regex(pattern="/^http:\/\//", message="URL must start with http://")
     */
    protected $url;

    /**
     * @MongoDB\Boolean
     * @Assert\Type(type="boolean", message="The value for enabled is not valid")
     */
    protected $enabled;

    /**
     * @MongoDB\Boolean
     * @Assert\Type(type="boolean", message="The value for up is not valid")
     */
    protected $up;

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
     * Set name
     *
     * @param string $name
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return self
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get url
     *
     * @return string $url
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return self
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean $enabled
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set up
     *
     * @param boolean $up
     * @return self
     */
    public function setUp($up)
    {
        $this->up = $up;
        return $this;
    }

    /**
     * Get up
     *
     * @return boolean $up
     */
    public function getUp()
    {
        return $this->up;
    }
}
