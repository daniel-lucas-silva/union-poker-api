<?php

use Phalcon\Debug;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Config\Adapter\Json;
use Phalcon\Mvc\Url;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Crypt;
use Firebase\JWT\JWT;
use PHPMailer\PHPMailer\PHPMailer;
use Phalcon\Cache\Frontend\Data as FrontendData;
use Phalcon\Cache\Backend\File as BackFile;

define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'production');

http_response_code(500);

if (APPLICATION_ENV === 'development') {
    ini_set('display_errors', "On");
    error_reporting(E_ALL);
    $debug = new Debug();
    $debug->listen();
    $debug->listenExceptions();
}

try {

    /**
     * Dependency Injector
     */
    $di = new FactoryDefault();

    $di->setShared('config', function () {
        return new Json(__DIR__ . "/../config.json");
    });

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    $di->setShared('url', function () {
        $config = $this->getConfig();

        $url = new Url();
        $url->setBaseUri($config->baseUri);
        return $url;
    });

    /**
     * Crypt service
     */
    $di->setShared('crypt', function () {
        $config = $this->getConfig();

        $crypt = new Crypt('des-ede3-cbc', true);
        $crypt->setKey($config->authentication->encryption_key);
        return $crypt;
    });

    /**
     * JWT service
     */
    $di->setShared('jwt', function () {
        return new JWT();
    });

    /**
     * Facebook service
     */
    $di->setShared('mailer', function () {
        $config = $this->getConfig();
        $mailerConfig = $config->mail->toArray();
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = APPLICATION_ENV === 'development' ? 2 : 1;   // Enable verbose debug output
        $mail->isSMTP();                                                // Set mailer to use SMTP
        $mail->Host       = $mailerConfig['host'];      // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                       // Enable SMTP authentication
        $mail->Username   = $mailerConfig['username'];                         // SMTP username
        $mail->Password   = $mailerConfig['password'];                                   // SMTP password
        $mail->SMTPSecure = $mailerConfig['encryption'];                                      // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = $mailerConfig['port'];
        $mail->setFrom($mailerConfig['from']['email'], $mailerConfig['from']['name']);
        return $mail;
    });

    /**
     * tokenConfig
     */
    $di->setShared('tokenConfig', function () {
        $config = $this->getConfig();

        $tokenConfig = $config->authentication->toArray();
        return $tokenConfig;
    });

    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    $di->setShared('db', function () {
        $config = $this->getConfig();

        $dbConfig = $config->database->toArray();

        $connection = new Mysql($dbConfig);
        $connection->setNestedTransactionsWithSavepoints(true);

        return $connection;
    });

    $di->setShared('db_log', function () {
        $config = $this->getConfig();

        $dbConfig = $config->log_database->toArray();

        $connection = new Mysql($dbConfig);
        $connection->setNestedTransactionsWithSavepoints(true);

        return $connection;
    });

    $di->setShared('modelsCache', function () {
        $frontCache = new FrontendData(['lifetime' => 86400]);
        $cache = new BackFile($frontCache, ['cacheDir' => __DIR__ . '/cache/']);
        return $cache;
    }
    );

    /**
     * Registering an autoloader
     */
    $loader = new Loader();

    $loader->registerNamespaces([
        'App\Models' => __DIR__ . "/models/",
        'App\Controllers' => __DIR__ . "/controllers/",
        'App\Common' => __DIR__ . "/common/",
        'App' => __DIR__ . "/library/",
    ]);

    $loader->register();

    require_once __DIR__ . "/routes.php";

} catch (Exception $e) {
    echo $e->getMessage();
    if (APPLICATION_ENV === 'development') {
        echo '<pre>' . $e->getTraceAsString() . '</pre>';
    }
}