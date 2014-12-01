<?php

namespace BBBLoadBalancer\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Exception\ValidatorException;

class UserAPIController extends Controller
{
    /**
     * @Route("api/users", name="users", defaults={"_format": "json"})
     * @Method({"GET"})
     */
    public function usersAction(Request $request)
    {
        $return = array(
            'users' => array()
        );

        // return active user
        if($request->get('active')){
            $active_user = $this->get('user')->getActiveUser();
            $return['users'][] = array(
                'id' => $active_user->getId(),
                'firstName' => $active_user->getFirstName(),
                'lastName' => $active_user->getLastName(),
                'email' => $active_user->getEmail(),
                'timezone' => $active_user->getTimezone(),
            );
            return new JsonResponse($return);
        }

        // return all users
        $users = $this->get('user')->getUsersBy(array());
        foreach($users as $user){
            $return['users'][] = array(
                'id' => $user->getId(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
                'email' => $user->getEmail(),
                'timezone' => $user->getTimezone(),
            );
        }

        return new JsonResponse($return);
    }

    /**
     * @Route("api/users", name="add_user", defaults={"_format": "json"})
     * @Method({"POST"})
     */
    public function addUserAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->get('user')->newUser();
        $user->setFirstName($data['user']['firstName']);
        $user->setLastName($data['user']['lastName']);
        $user->setEmail($data['user']['email']);
        $user->setTimezone($data['user']['timezone']);

        if(empty($data['user']['password1']) || empty($data['user']['password2'])){
            throw new ValidatorException("Please enter a password");
        }

        if($data['user']['password1'] != $data['user']['password2']){
            throw new ValidatorException("Passwords don't match");
        }
        $user->setPlainPassword($data['user']['password1']);
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        $password = $encoder->encodePassword($data['user']['password1'], $user->getSalt());
         // must be set for validation
        $user->setPassword($password);

        // validate user
        $errors = $this->get('validator')->validate($user);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage());
            }
        }

        $this->get('user')->saveUser($user);

        $return['user'] = array(
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'timezone' => $user->getTimezone(),
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("api/users/{id}", name="edit_user", defaults={"_format": "json"})
     * @Method({"PUT"})
     */
    public function editUserAction(Request $request, $id)
    {
        $user = $this->get('user')->getUserById($id);

        $data = json_decode($request->getContent(), true);

        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        $user->setFirstName($data['user']['firstName']);
        $user->setLastName($data['user']['lastName']);

        // password is set but does not match the repeat password
        if(!empty($data['user']['password1']) || !empty($data['user']['password2'])){
            if($data['user']['password1'] == $data['user']['password2']){
                $factory = $this->get('security.encoder_factory');
                $encoder = $factory->getEncoder($user);
                $password = $encoder->encodePassword($data['user']['password1'], $user->getSalt());
                $user->setPlainPassword($data['user']['password1']); // must be set for validation
                $user->setPassword($password);
            } else {
                throw new ValidatorException("Passwords don't match");
            }
        }

        // if password is set and matches repeat password


        // validate user
        $errors = $this->get('validator')->validate($user);
        if($errors->count()){
            foreach($errors as $error){
                throw new ValidatorException($error->getMessage());
            }
        }

        $this->get('user')->saveUser($user);

        $return['user'] = array(
            'id' => $user->getId(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'email' => $user->getEmail(),
            'timezone' => $user->getTimezone(),
        );

        return new JsonResponse($return);
    }

    /**
     * @Route("api/users/{id}", name="remove_user", defaults={"_format": "json"})
     * @Method({"DELETE"})
     */
    public function removeUserAction(Request $request, $id)
    {
        $user = $this->get('user')->getUserById($id);

        $active_user = $this->get('user')->getActiveUser();

        $data = json_decode($request->getContent(), true);

        if(!$user){
            throw new NotFoundHttpException("User not found");
        }

        if($active_user->getId() == $user->getId()){
            throw new NotFoundHttpException("It is not possible to remove the acitve user");
        }

        $this->get('user')->removeUser($user);

        $return = array();

        return new JsonResponse($return);
    }
}