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

class LoginPagesController extends Controller
{
    /**
     * login form.
     * @Route("/login", name="login")
     * @Template()
     */
    public function loginAction(){
        $request = $this->getRequest();
        $session = $request->getSession();

        // if authenticated, Go to the wall page, this redirect almost never heppens because frontpage redirects.
        if( $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') ) {
            return new RedirectResponse($this->get('router')->generate('dashboard'));
        }

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
                'error'         => $error
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
     * @Route("/logout/{group_machine_name}", name="logout", defaults={"group_machine_name":false})
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
}
