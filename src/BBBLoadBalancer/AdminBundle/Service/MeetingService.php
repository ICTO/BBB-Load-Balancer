<?php

namespace BBBLoadBalancer\AdminBundle\Service;

use BBBLoadBalancer\AdminBundle\Document\Meeting;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

class MeetingService
{
    protected $dm;
    protected $validator;

    /**
     * Constructor.
     */
    public function __construct($dm, $validator)
    {
        $this->dm = $dm->getManager();
        $this->validator = $validator;
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
        // validate meeting
        $errors = $this->validator->validate($meeting);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage());
            }
        }

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
