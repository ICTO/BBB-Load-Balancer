<?php

namespace BBBLoadBalancer\AdminBundle\Service;

use BBBLoadBalancer\AdminBundle\Document\Meeting;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MeetingService
{
    protected $dm;

    /**
     * Constructor.
     */
    public function __construct($dm)
    {
        $this->dm = $dm->getManager();
    }

    /**
     * Get meeting by id
     */
    public function getMeetingById($id){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Meeting')->find($id);
    }

    /**
     * Get Meetings by
     */
    public function getMeetingsBy($args, $orders = array(), $limit = null, $skip = null){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Meeting')->findBy($args, $orders, $limit, $skip);
    }

    /**
     * Get Meeting by args
     */
    public function getMeetingBy($args){
        return $this->dm->getRepository('BBBLoadBalancerAdminBundle:Meeting')->findOneBy($args);
    }

    /**
     * Save the Meeting
     */
    public function saveMeeting($meeting){
        $this->dm->persist($meeting);
        $this->dm->flush();
    }

    /**
     * remove the Meeting
     */
    public function removeMeeting($meeting){
        if (!$meeting) {
            throw new NotFoundHttpException();
        }

        $this->dm->remove($meeting);
        $this->dm->flush();
    }

    /**
     * New Meeting
     */
    public function newMeeting(){
        $meeting = new Meeting();
        return $meeting;
    }
}
