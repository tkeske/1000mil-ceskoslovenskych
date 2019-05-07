<?php

namespace App\Model;

use Nette;

class AuthorizatorFactory
{
    /**
     * @return Nette\Security\Permission
     */
    public static function create()
    {
        $acl = new Nette\Security\Permission;
        
        $acl->addRole('guest');
        $acl->addRole('user', 'guest');
        $acl->addRole('admin', 'user');
        $acl->addRole('kontrola', 'user');

        $acl->addResource('backend');

        $acl->allow('admin', 'backend');
        $acl->allow('kontrola', 'backend');
        $acl->deny('user', 'backend');
        $acl->deny('guest', 'backend');

        return $acl;
    }
}