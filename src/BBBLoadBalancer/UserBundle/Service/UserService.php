<?php

namespace BBBLoadBalancer\UserBundle\Service;

use BBBLoadBalancer\UserBundle\Document\User;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use \DateTime;
use \DateTimeZone;

class UserService
{
    protected $sc;
    protected $dm;
    protected $email_noreply;
    protected $email_name;
    protected $site_name;
    protected $mailer;
    protected $templating;

    /**
     * Constructor.
     */
    public function __construct($dm, $security_context, $mailer, $email_noreply, $email_name, $site_name, $templating)
    {
        $this->dm = $dm->getManager();
        $this->sc = $security_context;
        $this->mailer = $mailer;
        $this->email_noreply = $email_noreply;
        $this->email_name = $email_name;
        $this->site_name = $site_name;
        $this->templating = $templating;
    }

    /**
     * Get active User
     */
    public function getActiveUser(){
        return $this->sc->getToken()->getUser();
    }

    /**
     * Get user by username or email
     */
    public function getUserById($id){
        return $this->dm->getRepository('BBBLoadBalancerUserBundle:User')->find($id);
    }

    /**
     * Get user by username or email
     */
    public function getUsersBy($args, $orders = array(), $limit = null, $skip = null){
        return $this->dm->getRepository('BBBLoadBalancerUserBundle:User')->findBy($args, $orders, $limit, $skip);
    }

    /**
     * Get user by username or email
     */
    public function getUserBy($args){
        return $this->dm->getRepository('BBBLoadBalancerUserBundle:User')->findOneBy($args);
    }

    /**
     * Save the user
     */
    public function saveUser($user){
        $this->dm->persist($user);
        $this->dm->flush();
    }

    /**
     * Save the user
     */
    public function removeUser($user){
        if (!$user) {
            throw new NotFoundHttpException();
        }

        $this->dm->remove($user);
        $this->dm->flush();
    }

    /**
     * New user
     */
    public function newUser(){
        $user = new User();
        $user->setSalt(md5(uniqid(null, true)));
        $user->setRoles(array('ROLE_USER'));
        $user->setLocked(false);
        $user->setEnabled(true);
        $user->setDate(new DateTime('now', new DateTimeZone("UTC")));
        $user->setSecretKey($this->generateSecretKey());
        return $user;
    }

    /**
     * Create a unique username
     */
    public function uniqueUsername($firstName, $lastName){
        $count = 0;
        $username = $username_full = $this->cleanUsername($firstName . $lastName);

        while($user = $this->dm->getRepository('BBBLoadBalancerUserBundle:User')->findOneby(array('username' => $username_full))){
            if($count){
               $username_full = $username . $count;
            }
            $count++;
        }

        return $username_full;
    }

    /**
     * Send activation
     */
    public function sendActivation(User $user, $invitation_url = false){

        $subject = "Activate account on " . $this->site_name;

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($this->email_noreply => $this->email_name))
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'BBBLoadBalancerUserBundle:RegistrationPages:activationEmail.html.twig',
                    array(
                        'user' => $user,
                        'invitation_url' => $invitation_url,
                    )
                )
            , 'text/html');
        return $this->mailer->send($message);
    }

    /**
     * Send forget password mail
     */
    public function sendForgotPassword(User $user){

        $subject = "Change password on " . $this->site_name;

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array($this->email_noreply => $this->email_name))
            ->setTo($user->getEmail())
            ->setBody(
                $this->templating->render(
                    'BBBLoadBalancerUserBundle:RegistrationPages:forgotPasswordEmail.html.twig',
                    array(
                        'user' => $user
                    )
                )
            , 'text/html');
        return $this->mailer->send($message);
    }

    /**
     * User login
     *
     * Add the request parameter to get a redirect to the refered page.
     */
    public function userLogin($user, $request = false, $return_url = false){
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'user_login', $user->getRoles());
        $this->sc->setToken($token);
    }

    /**
     * User logout
     */
    public function userLogout(){
        $this->sc->setToken(null);
    }

    /**
     * Generate secret key
     */
    public function generateSecretKey($length = 25){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

    /**
     * Return the timezones
     */
    public function getTimezones(){
        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $zone) {
          date_default_timezone_set($zone);
          $zones_array[$zone] = '(GMT ' . date('P', $timestamp) . ") $zone";
        }
        return $zones_array;
    }

    /**
     * clean username
     */
    public function cleanUsername($string){
        $a = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
        $b = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
        $string = utf8_decode(strtolower($string));
        $string = strtr($string, utf8_decode($a), $b);
        $string = strtolower($string);
        return utf8_encode($string);
    }
}
