<?php
require __DIR__ . '/vendor/autoload.php';

ini_set('display_errors', '1');

$settings = require __DIR__ . '/src/settings.php';
$app = new \Slim\App($settings);

$router = new \RestApi\Router();
$router->install($app);

$app->run();
