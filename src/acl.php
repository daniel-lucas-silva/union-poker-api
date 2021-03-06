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

//$acl->addInherit('operator', 'player');
//$acl->addInherit('agent', 'player');
//$acl->addInherit('agent', 'operator');
//$acl->addInherit('admin', 'agent');
//$acl->addInherit('admin', 'manager');

$arrResources = [
    'guest' => [
        'Users' => ['login', 'recoverPassword', 'resetPassword', 'verifyRecoveryToken'],
    ],
    'player' => [],
    'operator' => [],
    'agent' => [],
    'manager' => [],
    'admin' => [
        'Banks' => ['search', 'delete', 'all', 'create', 'update', 'get'],
        'Clubs' => ['delete', 'all', 'create', 'update', 'get'],
        'TransactionsStatus' => ['delete', 'all', 'create', 'update', 'get'],
        'Users'     => ['delete', 'all', 'create', 'block', 'update'],
        'Players'   => ['delete', 'all', 'create', 'block', 'update'],
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
