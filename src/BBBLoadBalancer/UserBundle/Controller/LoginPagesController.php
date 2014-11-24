<?php

namespace BBBLoadBalancer\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Security\Core\SecurityContext;
use BBBLoadBalancer\UserBundle\Form\Type\LoginFormType;
use BBBLoadBalancer\UserBundle\Form\Type\ForgotPasswordFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use BBBLoadBalancer\UserBundle\Form\Type\ResetPasswordFormType;
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

        $form = $this->createForm(new LoginFormType());

        $form->get("_email")->setData($session->get(SecurityContext::LAST_USERNAME));

        return array(
                'form'          => $form->createView(),
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
        $form = $this->createForm(new ForgotPasswordFormType());
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            $user = $this->get('user')->getUserBy( array('email' => $form->get("email")->getData()));
            if(!is_object($user)){
                $form->get('email')->addError(new FormError("userbundle.controller.loginpages.forgotpassword.email_error"));
            }
            else if(!$user->getEnabled()){
                $invitation_url = $session->get('_security.user_login.target_path');
                $this->get('user')->sendActivation($user, $invitation_url);
                $form->get('email')->addError(new FormError("userbundle.controller.loginpages.forgotpassword.notenabled"));
            }
            else {
                if($this->get('user')->sendForgotPassword($user)){
                    $this->get('message')->success('userbundle.controller.loginpages.forgotpassword.check_email', array('%email%' => $user->getEmail()));
                }else{
                    $this->get('message')->error('userbundle.controller.loginpages.forgotpassword.sendemail_error');
                }

                return $this->get('ajaxify')->closePopup();
            }
        }
        return array(
            'form' => $form->createView()
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

        $form = $this->createForm(new ResetPasswordFormType(), $user);
        if($request->getMethod() == 'POST'){
            $form->bind($request);
            if($form->isValid()){
                // convert password
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($form->get("plainPassword")->getData(), $user->getSalt());
                $user->setPassword($password);
                $this->get('user')->saveUser($user);

                $this->get('message')->success('userbundle.controller.loginpages.resetpassword.changed');
            }
        }
        return array(
            'user_secret_key' => $user_secret_key,
            'user_id' => $user_id,
            'form' => $form->createView()
        );
    }
}
