<?php

use Doctrine\Common\Persistence\ObjectRepository;
use Nette\Object;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Passwords;
use Nette\Security as NS;


class MyAuthenticator implements NS\IAuthenticator
{
    
    use Nette\SmartObject;
    /** @var \Doctrine\ORM\EntityManager */
    private $users;
    
    public function __construct(\Doctrine\ORM\EntityManager $users)
    {
        $this->users = $users;
    }
    /**
     * Performs an authentication
     * @param  array
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;

        $user = $this->users->getRepository('App\User')->findBy(['email' => $username]);
        
        if (!$user) {
            throw new NS\AuthenticationException("User '$username' not found.", self::IDENTITY_NOT_FOUND);
        }

        $user = $user[0];

        if (!Passwords::verify($password, $user->password)) {
            throw new NS\AuthenticationException("Invalid password.", self::INVALID_CREDENTIAL);
        }
        return new NS\Identity($user->id, $user->role, $user->email);
    }
}