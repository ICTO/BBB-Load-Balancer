<?php

namespace BBBLoadBalancer\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use phpCAS;

class LoginPagesController extends Controller
{
    /**
     * login form.
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(){
        // redirect to setup if no users found.
        $user = $this->get('user')->getUserBy(array());
        if(!$user){
            return $this->redirect($this->generateUrl("setup"));
        }

        $request = $this->getRequest();
        $session = $request->getSession();

        // if authenticated, Go to the wall page, this redirect almost never heppens because frontpage redirects.
        //if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
        //    return new RedirectResponse($this->get('router')->generate('admin'));
        //}

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        return array(
                'last_username' => $session->get(SecurityContext::LAST_USERNAME),
                'error'         => $error,
                'cas'           => $this->container->hasParameter('cas_host')
        );
    }

    /**
     * Throw error if login check is not behind the firewall.
     * @Route("/login_check", name="login_check")
     * @throws \RuntimeException
     */
    public function checkAction(){
        throw new \RuntimeException();
    }
    /**
     * Throw error if logout path is not behind the firewall.
     * @Route("/logout", name="logout")
     * @throws \RuntimeException
     */
    public function logoutAction()
    {
        throw new \RuntimeException();
    }

    /**
     * Give a form to request a new password.
     * @Route("/forgot_password", name="forgot_password")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function forgotPasswordAction(Request $request){
        $email = "";
        $error = false;
        $success = false;

        if($request->getMethod() == 'POST'){
            $email = $request->get('email');
            $user = $this->get('user')->getUserBy( array('email' => $email));
            if(!is_object($user)){
                $error = "No user found with this email address.";
            }
            else {
                if($this->get('user')->sendForgotPassword($user)){
                    $success = "You received an email to change your password";
                }else{
                    $error = "Failed sending email.";
                }
            }
        }
        return array(
            'email' => $email,
            'error' => $error,
            'success' => $success
        );
    }

    /**
     * Form to change a users password.
     *
     * @Route("/password_reset/{user_secret_key}/{user_id}", name="password_reset")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function resetPasswordAction(Request $request, $user_secret_key, $user_id){
        $user = $this->get('user')->getUserById($user_id);
        if(!$user || $user->getSecretKey() != $user_secret_key){
            throw new AccessDeniedHttpException();
        }

        $user_errors = array();
        $error = false;
        $success = false;
        if($request->getMethod() == 'POST'){
            $plainPassword1 = $request->get('plainPassword1');
            $plainPassword2 = $request->get('plainPassword2');

            if($plainPassword1 != $plainPassword2){
                $error = "Passwords do not match";
            }
            else {
                $user->setPlainPassword($plainPassword1);
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($plainPassword1, $user->getSalt());
                $user->setPassword($password);

                // validate user
                $user_errors = $this->get('validator')->validate($user);
                if(!$user_errors->count()){
                    $this->get('user')->saveUser($user);
                    $success = "Changed password.";
                }
            }
        }
        return array(
            'user' => $user,
            'user_errors' => $user_errors,
            'error' => $error,
            'success' => $success,
        );
    }

    /**
     * Login with CAS
     * @Route("/login_cas", name="login_cas")
     */
    public function casLoginAction(){
        phpCAS::setDebug();

        phpCAS::client(CAS_VERSION_2_0, $this->container->getParameter('cas_host'), $this->container->getParameter('cas_port'), $this->container->getParameter('cas_context'));

        if(empty($this->container->getParameter('cas_host'))){
            phpCAS::setCasServerCACert($this->container->getParameter('cas_server_ca_cert_path'));
        } else {
            phpCAS::setNoCasServerValidation();
        }

        phpCAS::forceAuthentication();

        $casUid = phpCAS::getUser();
        $user = $this->get('user')->getUserBy( array('casUid' => $casUid));

        if(!$user){
            throw new AccessDeniedHttpException();
        }
        else {
            $this->get('user')->userLogin($user);
            return $this->redirect($this->generateUrl("admin"));
        }
    }
}
