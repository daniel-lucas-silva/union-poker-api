<?php

use App\Acl;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Mvc\Micro;
use Phalcon\Http\Request;

/*
 * Starting the application
 * Assign service locator to the application
 */
$app = new Micro($di);

/**
 * ACL checks
 * @noinspection PhpParamsInspection
 */
$app->before(new Acl());

/**
 * Users routes
 */
$users = new Collection();
$users->setHandler('App\Controllers\UsersController', true);
$users->setPrefix('/users');
$users->get('/', 'all');
$users->post('/', 'create');
$users->get('/{id}', 'get');
$users->patch('/{id}', 'update');
$users->delete('/{id}', 'delete');
$users->get('/me', 'me');
$users->post('/login', 'login');
$users->patch('/change-password', 'changePassword');
$users->post('/recover-password', 'recoverPassword');
$users->post('/reset-password', 'resetPassword');
$users->post('/verify-recovery-token', 'verifyRecoveryToken');
$app->mount($users);

/**
 * Banks routes
 */
$banks = new Collection();
$banks->setHandler('App\Controllers\BanksController', true);
$banks->setPrefix('/banks');
$banks->get('/', 'all');
$banks->post('/', 'create');
$banks->get('/{id}', 'get');
$banks->patch('/{id}', 'update');
$banks->delete('/{id}', 'delete');
$app->mount($banks);

/**
 * Clubs routes
 */
$clubs = new Collection();
$clubs->setHandler('App\Controllers\ClubsController', true);
$clubs->setPrefix('/clubs');
$clubs->get('/', 'all');
$clubs->post('/', 'create');
$clubs->get('/{id}', 'get');
$clubs->patch('/{id}', 'update');
$clubs->delete('/{id}', 'delete');
$app->mount($clubs);

/**
 * Not Found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    $app->response->setContentType('application/json', 'UTF-8');
    $app->response->setJsonContent([
        "status" => "error",
        "code" => "404",
        "messages" => "URL Not found",
    ]);
    $app->response->send();
});

/**
 * Error handler
 */
$app->error(function ($exception) use ($app) {
    if (APPLICATION_ENV != 'development') {
        $app->response->setStatusCode(500, "Internal Server Error")->sendHeaders();
        $app->response->setContentType('application/json', 'UTF-8');
        $app->response->setJsonContent([
            "status" => "error",
            "code" => "500",
            "messages" => "Internal Server Error"
        ]);
        $app->response->send();
        exit;
    }
    return $exception;
});

$app->handle();