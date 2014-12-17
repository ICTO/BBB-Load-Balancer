<?php

namespace BBBLoadBalancer\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AdminPagesController extends Controller
{
    /**
     * @Route("/admin", name="admin")
     * @Method({"GET"})
     * @Template()
     */
    public function adminAction()
    {
        $user = $this->get('user')->getActiveUser();
        $timezone = new \DateTimeZone($user->getTimezone());
        $offset = 0 - ($timezone->getOffset(new \DateTime("now", new \DateTimeZone("UTC")))/60);

        // This page return an EmberJS application
        return array(
            'user' => $user,
            'timezoneOffset' => $offset
        );
    }

    /**
     * @Route("/setup", name="setup")
     * @Method({"GET","POST"})
     * @Template()
     */
    public function setupAction(Request $request)
    {
    	// is there already a user in our system?
    	$user_found = $this->get('user')->getUserBy(array());
    	if($user_found){
    		return array(
    			'setup_done' => true,
    		);
    	}

        $errors = array();

    	if($request->getMethod() == 'POST'){
    		$name = $request->get('name');
            $password = $request->get('password');

            $user = $this->get('user')->newUser();

            // convert password
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $password = $encoder->encodePassword($request->get("plainPassword"), $user->getSalt());

            // set generated values
            $user->setFirstName($request->get("firstName"));
            $user->setLastName($request->get("lastName"));
            $user->setTimezone($request->get("timezone"));
            $user->setPlainPassword($request->get("plainPassword")); // must be set for validation
            $user->setPassword($password);
            $user->setEmail(strtolower($request->get("email")));
            $username = $this->get("user")->uniqueUsername($request->get("firstName"), $request->get("lastName"));
            $user->setUsername($username);

            // validate user
            $errors = $this->get('validator')->validate($user);
            if(!$errors->count()){
                $this->get('user')->saveUser($user);
                $this->get('user')->userLogin($user);
                return $this->redirect($this->generateUrl("admin"));
            }
    	}

        return array(
        	'setup_done' => false,
            'errors' => $errors,
            'timezones' => $this->get('user')->getTimezones(),
            'default_timezone' => date_default_timezone_get(),
        );
    }
}
