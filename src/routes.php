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
 * Index routes
 */
$index = new Collection();
$index->setHandler('App\Controllers\IndexController', true);
$index->get('/', 'index');
$app->mount($index);

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
 * Categories routes
 */
$categories = new Collection();
$categories->setHandler('App\Controllers\CategoriesController', true);
$categories->setPrefix('/categories');
$categories->get('/', 'all');
$categories->post('/', 'create');
$categories->get('/{id}', 'get');
$categories->patch('/{id}', 'update');
$categories->delete('/{id}', 'delete');
$app->mount($categories);

/**
 * Not Found handler
 */
$app->notFound(function () use ($app) {
    $app->response->setStatusCode(404, "Not Found")->sendHeaders();
    $app->response->setContentType('application/json', 'UTF-8');
    $app->response->setJsonContent(array(
        "status" => "error",
        "code" => "404",
        "messages" => "URL Not found",
    ));
    $app->response->send();
});

/**
 * Error handler
 */
$app->error(function ($exception) use ($app) {
    if (APPLICATION_ENV != 'development') {
        $app->response->setStatusCode(500, "Internal Server Error")->sendHeaders();
        $app->response->setContentType('application/json', 'UTF-8');
        $app->response->setJsonContent(array(
            "status" => "error",
            "code" => "500",
            "messages" => "Internal Server Error"
        ));
        $app->response->send();
        exit;
    }
    return $exception;
});

//$request = new Request();
//$app->handle($request->getURI());
$app->handle();