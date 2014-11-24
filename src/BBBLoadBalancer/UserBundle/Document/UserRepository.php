<?php

namespace BBBLoadBalancer\UserBundle\Document;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class UserRepository extends DocumentRepository implements UserProviderInterface
{
    // this function is called by the firewall. We will load the user by email instead
    public function loadUserByUsername($username)
    {
        $q = $this->createQueryBuilder('u');
        $user = $q->addOr($q->expr()->field('username')->equals($username))
          ->addOr($q->expr()->field('email')->equals($username))
          ->getQuery()
          ->getSingleResult();

        if($user){
            return $user;
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException();
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getDocumentName() === $class
            || is_subclass_of($class, $this->getDocumentName());
    }
}
