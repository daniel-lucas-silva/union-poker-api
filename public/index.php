<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, DELETE, PATCH');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: X-Requested-With');
header('Access-Control-Allow-Headers: User-Agent,Keep-Alive,Content-Type,Authorization');
header("Cache-Control: public, s-maxage=120");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && (
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' ||
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'DELETE' ||
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'PATCH' )) {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");
        header('Access-Control-Allow-Headers: X-Requested-With');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PATCH');
        header('Access-Control-Max-Age: 86400');
    }
    exit;
}

require_once __DIR__ . "/../vendor/autoload.php";
require_once __DIR__ . "/../src/bootstrap.php";
