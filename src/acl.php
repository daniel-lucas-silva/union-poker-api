<?php

use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;

$acl = new Memory();

$acl->setDefaultAction(Acl::DENY);

$acl->addRole(new Role('guest'));
$acl->addRole(new Role('player'));
$acl->addRole(new Role('operator'));
$acl->addRole(new Role('agent'));
$acl->addRole(new Role('manager'));
$acl->addRole(new Role('admin'));

$acl->addInherit('operator', 'player');
$acl->addInherit('agent', 'player');
$acl->addInherit('agent', 'operator');
$acl->addInherit('admin', 'agent');
$acl->addInherit('admin', 'manager');

$arrResources = [
    'guest' => [
        'Index' => ['index'],
        'ContactUs' => ['send'],
        'Users' => ['profile', 'login', 'register', 'facebook', 'google', 'changePassword'],
        'Posts' => ['all', 'get'],
        'Images' => ['all', 'get'],
        'Pages' => ['all', 'get'],
        'Sliders' => ['all', 'get'],
        'Reports' => ['create'],
        'Tags' => ['all', 'get'],
        'Categories' => ['all', 'get'],
        'Comments' => ['all', 'get'],
        'Reactions' => ['get'],
    ],
    'player' => [
        'Users' => ['me', 'update', 'resetPassword'],
        'Searches' => ['all', 'create'],
        'Posts' => ['react', 'reactions'],
        'Comments' => ['create', 'update', 'delete', 'react', 'reactions'],
        'Reactions' => ['create', 'update', 'delete'],
    ],
    'operator' => [
        'Posts' => ['create', 'update'],
        'Images' => ['create', 'update'],
    ],
    'agent' => [
        'Posts' => ['create', 'update'],
        'Images' => ['create', 'update'],
    ],
    'manager' => [
        'Posts' => ['create', 'update'],
        'Images' => ['create', 'update'],
    ],
    'admin' => [
        'Users' => ['delete', 'create', 'block'],
        'ContactUs' => ['all', 'get', 'update', 'delete'],
        'Posts' => ['delete'],
        'Images' => ['delete'],
        'Pages' => ['create', 'update', 'delete'],
        'Tags' => ['create', 'update', 'delete'],
        'Categories' => ['create', 'update', 'delete'],
        'Reactions' => ['all', 'get'],
        'Reports' => ['all', 'get', 'update', 'delete'],
    ],
];

foreach ($arrResources as $arrResource) {
    foreach ($arrResource as $controller => $arrMethods) {
        $acl->addResource(new Resource($controller), $arrMethods);
    }
}

foreach ($acl->getRoles() as $objRole) {
    $roleName = $objRole->getName();

    foreach ($arrResources['guest'] as $resource => $method) {
        $acl->allow($roleName, $resource, $method);
    }

    if ($roleName == 'player') {
        foreach ($arrResources['player'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }

    if ($roleName == 'operator') {
        foreach ($arrResources['operator'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }

    if ($roleName == 'agent') {
        foreach ($arrResources['agent'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }

    if ($roleName == 'manager') {
        foreach ($arrResources['manager'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }

    if ($roleName == 'admin') {
        foreach ($arrResources['admin'] as $resource => $method) {
            $acl->allow($roleName, $resource, $method);
        }
    }
}
